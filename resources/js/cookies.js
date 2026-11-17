/**
 * Cookie consent + Google Analytics 4.
 *
 * Privacy-first: nothing from Google loads until the visitor clicks "Accept".
 * "Reject" is just as prominent as "Accept". Visitors can change their choice
 * any time via the "Cookie settings" link in the footer.
 */
const analyticsId = document.querySelector('meta[name="analytics-id"]')?.content;

if (analyticsId) {
    Promise.all([import('vanilla-cookieconsent'), import('vanilla-cookieconsent/dist/cookieconsent.css')]).then(([CookieConsent]) => {
        let analyticsLoaded = false;

        const gtag = (...args) => {
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push(args);
        };

        const applyChoice = () => {
            const accepted = CookieConsent.acceptedCategory('analytics');

            if (accepted && !analyticsLoaded) {
                gtag('consent', 'update', { analytics_storage: 'granted' });
                const script = document.createElement('script');
                script.async = true;
                script.src = `https://www.googletagmanager.com/gtag/js?id=${encodeURIComponent(analyticsId)}`;
                document.head.appendChild(script);
                gtag('js', new Date());
                gtag('config', analyticsId);
                analyticsLoaded = true;
            } else if (!accepted && analyticsLoaded) {
                // Consent withdrawn: stop analytics. Old _ga cookies are removed automatically.
                gtag('consent', 'update', { analytics_storage: 'denied' });
                window.location.reload();
            }
        };

        CookieConsent.run({
            cookie: { name: 'cookie_consent', expiresAfterDays: 182 },
            revision: 1,
            guiOptions: {
                consentModal: { layout: 'box inline', position: 'bottom left', equalWeightButtons: true, flipButtons: false },
                preferencesModal: { layout: 'box', equalWeightButtons: true },
            },
            categories: {
                necessary: { enabled: true, readOnly: true },
                analytics: {
                    autoClear: { cookies: [{ name: /^_ga/ }] },
                },
            },
            onConsent: applyChoice,
            onChange: applyChoice,
            language: {
                default: 'en',
                translations: {
                    en: {
                        consentModal: {
                            title: 'Cookies on this website',
                            description:
                                'We use essential cookies to make the website work. With your permission, we would also like to use Google Analytics cookies to understand how visitors use the site. Nothing is shared for advertising.',
                            acceptAllBtn: 'Accept',
                            acceptNecessaryBtn: 'Reject',
                            showPreferencesBtn: 'Choose',
                            footer: '<a href="/privacy-policy">Privacy policy</a>',
                        },
                        preferencesModal: {
                            title: 'Cookie settings',
                            acceptAllBtn: 'Accept all',
                            acceptNecessaryBtn: 'Reject all',
                            savePreferencesBtn: 'Save my choice',
                            closeIconLabel: 'Close',
                            sections: [
                                {
                                    title: 'Essential cookies',
                                    description: 'Keep the website secure and remember your cookie choice. These cannot be switched off.',
                                    linkedCategory: 'necessary',
                                },
                                {
                                    title: 'Analytics cookies (Google Analytics)',
                                    description:
                                        'Help us see which pages are useful, using anonymous statistics. Cookies: _ga, _ga_*. Kept for up to 14 months.',
                                    linkedCategory: 'analytics',
                                },
                                {
                                    title: 'More information',
                                    description: 'See our <a href="/privacy-policy">privacy policy</a> (English and Bahasa Melayu).',
                                },
                            ],
                        },
                    },
                },
            },
        });
    });
}
