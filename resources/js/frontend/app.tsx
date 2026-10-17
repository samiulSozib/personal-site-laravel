import '../../css/frontend.css';

import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRoot, hydrateRoot } from 'react-dom/client';

// Entry for the public, bilingual site. The dashboard uses resources/js/dashboard/app.jsx.
createInertiaApp({
    title: (title) => (title ? `${title}` : ''),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.tsx`,
            import.meta.glob('./pages/*.tsx'),
        ),
    setup({ el, App, props }) {
        // The server already rendered this page (see ssr.tsx), so attach to that markup.
        if (el.hasChildNodes()) {
            hydrateRoot(el, <App {...props} />);
            return;
        }

        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#4B5563',
    },
});
