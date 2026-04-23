<button
    type="button"
    id="start-admin-tour"
    class="inline-flex items-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-medium text-white hover:bg-primary-500"
>
    Guide
</button>

<script src="https://cdn.jsdelivr.net/npm/shepherd.js@11.2.0/dist/js/shepherd.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/shepherd.js@11.2.0/dist/css/shepherd.css" />

<script>
    (function () {
        const startButton = document.getElementById('start-admin-tour');
        if (!startButton || !window.Shepherd) {
            return;
        }

        const tour = new Shepherd.Tour({
            defaultStepOptions: {
                cancelIcon: { enabled: true },
                scrollTo: { behavior: 'smooth', block: 'center' },
                classes: 'shadow-xl rounded-lg',
            },
            useModalOverlay: true,
        });

        const addStep = (id, title, text, selector, on = 'right') => {
            tour.addStep({
                id,
                title,
                text,
                attachTo: selector ? { element: selector, on } : undefined,
                buttons: [
                    {
                        text: 'Précédent',
                        action: tour.back,
                        classes: 'shepherd-button-secondary',
                    },
                    {
                        text: 'Suivant',
                        action: tour.next,
                    },
                ],
            });
        };

        addStep(
            'welcome',
            'Bienvenue dans le dashboard',
            'Ce guide vous présente les menus principaux et leur utilité pour piloter KinTaxi.',
            '.fi-topbar',
            'bottom'
        );

        addStep(
            'global-search',
            'Recherche globale',
            'Utilisez cette barre pour rechercher rapidement des utilisateurs, courses, véhicules et autres ressources.',
            '.fi-global-search-field',
            'bottom'
        );

        const menuDescriptions = {
            users: 'Gérez les comptes clients/chauffeurs, leurs profils, rôles et statut KYC.',
            rides: 'Suivez les courses, consultez les détails et le suivi temps réel.',
            'kyc-verifications': 'Consultez les vérifications Smile ID et les statuts.',
            'pricing-rules': 'Définissez les règles tarifaires par type, unité, zone et catégorie.',
            zones: 'Créez et visualisez les zones géographiques.',
            vehicles: 'Gérez les véhicules et leurs caractéristiques.',
            documents: 'Contrôlez les documents soumis et leur validation.',
            payments: 'Suivez les paiements des courses.',
            transactions: 'Consultez les mouvements financiers de la plateforme.',
            reviews: 'Consultez la qualité de service via les avis.',
            countries: 'Maintenez la liste des pays disponibles.',
            currencies: 'Configurez les devises et leur usage.',
            statuses: 'Administrez les statuts utilisés dans l’application.',
            'user-roles': 'Gérez les rôles applicatifs des utilisateurs.',
            'vehicle-categories': 'Définissez les catégories de véhicules.',
            'vehicle-shapes': 'Définissez les formes/types de véhicules.',
            'vehicle-features': 'Administrez les fonctionnalités des véhicules.',
            'payment-methods': 'Configurez les moyens de paiement disponibles.',
            'payment-gateways': 'Configurez les passerelles de paiement.',
            media: 'Gérez les médias uploadés.',
            'app-notifications': 'Pilotez les notifications applicatives.',
            'live-ride-tracking': 'Suivez les courses en direct sur la carte.',
        };

        const sidebarLinks = Array.from(
            document.querySelectorAll('.fi-sidebar a[href*="/admin/"]')
        ).filter((link) => {
            const href = link.getAttribute('href') || '';
            return !href.includes('/create') && !href.includes('/edit') && !href.includes('/shield/roles/');
        });

        const seen = new Set();
        sidebarLinks.forEach((link) => {
            const href = link.getAttribute('href') || '';
            const slug = href.split('/admin/')[1]?.split('/')[0] || '';
            if (!slug || seen.has(slug)) {
                return;
            }
            seen.add(slug);

            const label = (link.textContent || '').trim() || slug;
            const description = menuDescriptions[slug] || 'Ce menu permet de gérer les données liées à ce module.';

            addStep(
                `menu-${slug}`,
                `Menu ${label}`,
                description,
                `a[href*="/admin/${slug}"]`,
                'right'
            );
        });

        tour.addStep({
            id: 'finish',
            title: 'Guide terminé',
            text: 'Vous pouvez relancer ce guide à tout moment via le bouton "Guide" en haut.',
            buttons: [
                {
                    text: 'Terminer',
                    action: tour.complete,
                },
            ],
        });

        startButton.addEventListener('click', () => tour.start());

        const autoRunKey = 'kintaxi_admin_tour_seen';
        if (! localStorage.getItem(autoRunKey)) {
            localStorage.setItem(autoRunKey, '1');
            setTimeout(() => tour.start(), 900);
        }
    })();
</script>
