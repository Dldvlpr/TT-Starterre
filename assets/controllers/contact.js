class ContactFormValidator {
    constructor(formSelector) {
        this.form = document.querySelector(formSelector);
        this.errors = new Map();
        this.init();
    }

    init() {
        if (!this.form) return;
        this.form.addEventListener('submit', (e) => this.handleSubmit(e));
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
                sanitized = sanitized.replace(/[^\d\s\.\-\+]/g, '');
                break;

            case 'postal_code':
                sanitized = sanitized.replace(/\D/g, '');
                break;

            case 'email':
                sanitized = sanitized.toLowerCase();
                break;
        }

        return sanitized
    }

    validateAll(data) {
        this.errors.clear();

        const requiredFields = ['name', 'lastname', 'email', 'phone', 'address', 'postal_code', 'city'];

        requiredFields.forEach(field => {
            if (!data[field] || data[field].trim() === '') {
                this.errors.set(field, 'Ce champ est requis');
            }
        });
        if (data.email && !this.isValidEmail(data.email)) {
            this.errors.set('email', 'Format invalide');
        }
        if (data.phone && !this.isValidPhone(data.phone)) {
            this.errors.set('phone', 'Format invalide');
        }
        if (data.postal_code && !this.isValidPostalCode(data.postal_code)) {
            this.errors.set('postal_code', 'Code postal invalide (5 chiffres)');
        }

        if (!data.gender) {
            this.errors.set('gender', 'Veuillez sélectionner votre genre');
        } else if (!['male', 'female'].includes(data.gender)) {
            this.errors.set('gender', 'Genre invalide');
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
            const response = await fetch('/contact', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            if (response.ok) {
                alert('Message envoyé avec succès !');
                this.form.reset();
            } else {
                throw new Error('Erreur serveur');
            }
        } catch (error) {
            console.error('Erreur:', error);
            alert('Une erreur est survenue. Veuillez réessayer.');
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new ContactFormValidator('form');
});
