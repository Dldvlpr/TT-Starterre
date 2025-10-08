class ContactFormValidator {
    constructor(formSelector) {
        this.form = document.querySelector(formSelector);
        this.errors = new Map();
        this.init();
    }

    init() {
        if (!this.form) return;
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
        this.postalCodeAutoComplete();
        this.setupFormToggle();
    }

    async handleSubmit(e) {
        e.preventDefault();
        this.clearErrors();

        const data = this.collectAndSanitizeData();

        if (!this.validateAll(data)) {
            this.displayErrors();
            return;
        }

        await this.submitToServer(data);
    }

    collectAndSanitizeData = () => {
        const formData = new FormData(this.form);
        const sanitizedData = {};

        for (let [key, value] of formData.entries()) {
            sanitizedData[key] = this.sanitizeField(key, value);
        }

        return sanitizedData;
    }

    sanitizeField(fieldName, value) {
        if (typeof value !== 'string') return value;

        let sanitized = value
            .trim()
            .replace(/[<>]/g, '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
                .replace(/'/g, '&#x27;');

        switch (fieldName) {
            case 'phone':
            case 'companyPhone':
                sanitized = sanitized.replace(/[^\d\s\.\-\+]/g, '');
                break;

            case 'postalCode':
            case 'companyPostalCode':
                sanitized = sanitized.replace(/\D/g, '');
                break;

            case 'email':
            case 'companyEmail':
                sanitized = sanitized.toLowerCase();
                break;
        }

        return sanitized
    }

    validateAll(data) {
        this.errors.clear();

        const isCompanyForm = document.getElementById('company').checked;

        let requiredFields;
        if (isCompanyForm) {
            requiredFields = ['siret', 'companyName', 'companyEmail', 'companyPhone', 'companyAddress', 'companyPostalCode', 'companyCity'];
        } else {
            requiredFields = ['name', 'lastname', 'email', 'phone', 'address', 'postalCode', 'city'];

            if (!data.gender) {
                this.errors.set('gender', 'Veuillez sélectionner votre genre');
            } else if (!['male', 'female'].includes(data.gender)) {
                this.errors.set('gender', 'Genre invalide');
            }
        }
        requiredFields.forEach(field => {
            if (!data[field] || data[field].trim() === '') {
                this.errors.set(field, 'Ce champ est requis');
            }
        });
        const emailField = isCompanyForm ? 'companyEmail' : 'email';
        if (data[emailField] && !this.isValidEmail(data[emailField])) {
            this.errors.set(emailField, 'Format email invalide');
        }

        const phoneField = isCompanyForm ? 'companyPhone' : 'phone';
        if (data[phoneField] && !this.isValidPhone(data[phoneField])) {
            this.errors.set(phoneField, 'Format téléphone invalide');
        }

        const postalField = isCompanyForm ? 'companyPostalCode' : 'postalCode';
        if (data[postalField] && !this.isValidPostalCode(data[postalField])) {
            this.errors.set(postalField, 'Code postal invalide (5 chiffres)');
        }
        if (isCompanyForm && data.siret && !this.isValidSiret(data.siret)) {
            this.errors.set('siret', 'SIRET invalide (14 chiffres)');
        }

        return this.errors.size === 0;
    }

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    isValidPhone(phone) {
        return /^(?:(?:\+|00)33|0)[1-9](?:[\s.-]*\d{2}){4}$/.test(phone.replace(/\s/g, ''));
    }

    isValidPostalCode(postalCode) {
        return /^\d{5}$/.test(postalCode);
    }

    isValidSiret(siret) {
        return /^\d{14}$/.test(siret.replace(/\s/g, ''));
    }

    clearErrors = () => {
        this.errors.clear();

        this.form.querySelectorAll('.is-invalid').forEach(field => {
            field.classList.remove('is-invalid');
        });

        this.form.querySelectorAll('.invalid-feedback').forEach(error => {
            error.remove();
        });
        this.form.querySelectorAll('input[name="gender"]').forEach(radio => {
            radio.closest('.form-group').classList.remove('is-invalid');
        });
    }

    displayErrors() {
        this.errors.forEach((message, fieldName) => {
            let field = this.form.querySelector(`#${fieldName}`);

            if (fieldName === 'gender') {
                field = this.form.querySelector('input[name="gender"]').closest('.form-group');
            }

            if (field) {
                field.classList.add('is-invalid');

                const errorDiv = document.createElement('div');
                errorDiv.className = 'invalid-feedback';
                errorDiv.textContent = message;

                field.parentNode.insertBefore(errorDiv, field.nextSibling);
            }
        });
    }

    async submitToServer(data) {
        try {
            const response = await fetch('/contact/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (response.ok) {
                this.form.reset();
            } else {
                throw new Error(result.error || 'Erreur serveur');
            }
        } catch (error) {
            alert('Une erreur est survenue. Veuillez réessayer.');
        }
    }

    setupFormToggle() {
        const personRadio = document.getElementById('person');
        const companyRadio = document.getElementById('company');
        const personForm = document.getElementById('person-form');
        const companyForm = document.getElementById('company-form');

        const toggleForms = () => {
            if (personRadio.checked) {
                personForm.style.display = 'block';
                companyForm.style.display = 'none';
            } else if (companyRadio.checked) {
                companyForm.style.display = 'block';
                personForm.style.display = 'none';
            }
        };

        personRadio.addEventListener('change', toggleForms);
        companyRadio.addEventListener('change', toggleForms);

        toggleForms();
    }

    postalCodeAutoComplete() {
        const postalCodeField = document.getElementById('postalCode');
        const companyPostalCodeField = document.getElementById('companyPostalCode');

        if (postalCodeField) {
            postalCodeField.addEventListener('input', (e) => {
                this.handlePostalCodeInput(e.target, 'city');
            });
        }
    }

    async handlePostalCodeInput(postalField, cityFieldId) {
        const postalCode = postalField.value.trim();

        if (postalCode < 5) {
            return;
        }

        try {
            const response = await fetch(`https://geo.api.gouv.fr/communes?codePostal=${postalCode}&fields=nom`);
            const communes = await response.json();

            if (communes.length === 1) {
                const cityField = document.getElementById(cityFieldId);
                if (cityField) {
                    cityField.value = communes[0].nom;
                }
            }

        } catch (error) {
            console.log('Erreur API Géo:', error);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new ContactFormValidator('form');
});
