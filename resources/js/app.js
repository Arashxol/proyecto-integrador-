import './app.css';
import React from 'react';
import ReactDOM from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';

createInertiaApp({
    id: 'app',
    resolvePage: (name) => import(`./Pages/${name}.jsx`),
    setup({ el, App, props }) {
        ReactDOM.createRoot(el).render(
            <React.StrictMode>
                <App {...props} />
            </React.StrictMode>
        );
    },
});
