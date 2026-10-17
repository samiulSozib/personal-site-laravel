import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import ReactDOMServer from 'react-dom/server';

// Renders the public site on the server, so the first HTML response already has the page content,
// the page title, and the meta tags. The browser then hydrates the same markup (see app.tsx).
createServer((page) =>
    createInertiaApp({
        page,
        render: ReactDOMServer.renderToString,
        title: (title) => (title ? `${title}` : ''),
        resolve: (name) =>
            resolvePageComponent(
                `./pages/${name}.tsx`,
                import.meta.glob('./pages/*.tsx'),
            ),
        setup: ({ App, props }) => <App {...props} />,
    }),
);
