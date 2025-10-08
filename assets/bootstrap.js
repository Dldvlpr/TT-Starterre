import { startStimulusApp } from '@symfony/stimulus-bridge';

import 'bootstrap/dist/css/bootstrap.min.css';

import 'bootstrap';

const app = startStimulusApp();

// Auto-register all controllers from the controllers directory
app.load(
    definitionsFromContext(
        require.context('./controllers', true, /_controller\.js$/)
    )
);

// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);

function definitionsFromContext(context) {
    return context.keys()
        .map((key) => definitionForModuleWithContextAndKey(context, key))
        .filter((value) => value);
}

function definitionForModuleWithContextAndKey(context, key) {
    const identifier = identifierForContextKey(key);
    if (identifier) {
        return {
            identifier,
            controllerConstructor: context(key).default
        };
    }
}

function identifierForContextKey(key) {
    const logicalName = key.replace(/(^\.\/|_controller\.js$)/g, '');
    return logicalName.replace(/_/g, '-');
}
