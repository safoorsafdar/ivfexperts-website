(function () {

    function initApp() {

        /* ===============================
           ROTATING HERO TEXT
        =============================== */
        var texts = [
            "Advanced IVF & ICSI Treatment in Lahore",
            "Personalized Fertility Care Across Pakistan",
            "Male & Female Infertility Expertise",
            "Teleconsultations for Overseas Pakistanis"
        ];

        var index = 0;
        var rotating = document.getElementById("rotating-text");

        if (rotating) {
            setInterval(function () {
                rotating.style.opacity = 0;
                rotating.style.transform = "translateY(10px)";

                setTimeout(function () {
                    index = (index + 1) % texts.length;
                    rotating.innerText = texts[index];
                    rotating.style.opacity = 1;
                    rotating.style.transform = "translateY(0)";
                }, 500);
            }, 4500);
        }

        /* ===============================
           STICKY HEADER SHADOW
        =============================== */
        var header = document.querySelector("header");

        if (header) {
            window.addEventListener("scroll", function () {
                if (window.scrollY > 80) {
                    header.classList.add("header-shrink");
                } else {
                    header.classList.remove("header-shrink");
                }
            });
        }

        /* ===============================
           ADVANCED SCROLL REVEAL
        =============================== */
        var fadeElements = document.querySelectorAll(".fade-in");

        if ("IntersectionObserver" in window && fadeElements.length > 0) {
            var observer = new IntersectionObserver(function (entries, obs) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add("appear");
                        obs.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1, rootMargin: "0px 0px -50px 0px" });

            fadeElements.forEach(function (el) {
                observer.observe(el);
            });
        } else {
            fadeElements.forEach(function (el) { el.classList.add("appear"); });
        }

        /* ===============================
           COUNTER ANIMATION
        =============================== */
        var counters = document.querySelectorAll(".counter");

        counters.forEach(function (counter) {
            var target = parseInt(counter.getAttribute("data-target"));
            if (isNaN(target)) return;

            var started = false;

            var runCounter = function () {
                if (started) return;
                started = true;

                var current = 0;
                var increment = target / 60;

                var update = function () {
                    current += increment;

                    if (current < target) {
                        counter.innerText = Math.ceil(current);
                        requestAnimationFrame(update);
                    } else {
                        counter.innerText = target + "+";
                    }
                };

                update();
            };

            if ("IntersectionObserver" in window) {
                var counterObserver = new IntersectionObserver(function (entries, obs) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            runCounter();
                            obs.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.5 });

                counterObserver.observe(counter);
            } else {
                runCounter();
            }
        });

        /* ===============================
           PREMIUM CARD HOVER GLOW
        =============================== */
        var cards = document.querySelectorAll(".card");
        cards.forEach(function (card) {
            card.addEventListener("mousemove", function (e) {
                var rect = card.getBoundingClientRect();
                var x = e.clientX - rect.left;
                var y = e.clientY - rect.top;

                card.style.setProperty("--mouse-x", x + "px");
                card.style.setProperty("--mouse-y", y + "px");
            });
        });

        /* ===============================
           MOBILE MENU
        =============================== */
        var mobileMenuBtn = document.getElementById("mobile-menu-btn");
        var mobileMenuClose = document.getElementById("mobile-menu-close");
        var mobileMenuOverlay = document.getElementById("mobile-menu-overlay");
        var mobileMenuBackdrop = document.getElementById("mobile-menu-backdrop");
        var mobileMenuPanel = document.getElementById("mobile-menu-panel");

        function openMobileMenu() {
            if (!mobileMenuOverlay) return;
            mobileMenuOverlay.style.display = "block";
            document.body.style.overflow = "hidden";
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    mobileMenuBackdrop.style.opacity = "1";
                    mobileMenuPanel.style.transform = "translateX(0)";
                });
            });
        }

        function closeMobileMenu() {
            if (!mobileMenuOverlay) return;
            mobileMenuBackdrop.style.opacity = "0";
            mobileMenuPanel.style.transform = "translateX(100%)";
            document.body.style.overflow = "";
            setTimeout(function () {
                mobileMenuOverlay.style.display = "none";
            }, 300);
        }

        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener("click", openMobileMenu);
        }
        if (mobileMenuClose) {
            mobileMenuClose.addEventListener("click", closeMobileMenu);
        }
        if (mobileMenuBackdrop) {
            mobileMenuBackdrop.addEventListener("click", closeMobileMenu);
        }

        // Close menu when a nav link is clicked
        if (mobileMenuOverlay) {
            var menuLinks = mobileMenuOverlay.querySelectorAll("nav a");
            menuLinks.forEach(function (link) {
                link.addEventListener("click", closeMobileMenu);
            });
        }

        // Accordion toggles
        var accordionToggles = document.querySelectorAll(".mobile-accordion-toggle");
        accordionToggles.forEach(function (toggle) {
            toggle.addEventListener("click", function () {
                var accordion = toggle.closest(".mobile-accordion");
                var content = accordion.querySelector(".mobile-accordion-content");
                var arrow = accordion.querySelector(".mobile-accordion-arrow");
                var isOpen = content.style.display === "block";

                // Close all others first
                accordionToggles.forEach(function (otherToggle) {
                    var otherAccordion = otherToggle.closest(".mobile-accordion");
                    var otherContent = otherAccordion.querySelector(".mobile-accordion-content");
                    var otherArrow = otherAccordion.querySelector(".mobile-accordion-arrow");
                    otherContent.style.display = "none";
                    otherArrow.style.transform = "rotate(0deg)";
                });

                // Toggle current
                if (!isOpen) {
                    content.style.display = "block";
                    arrow.style.transform = "rotate(180deg)";
                }
            });
        });

    }

    // Handle both cases: DOM already loaded, or still loading
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initApp);
    } else {
        initApp();
    }

})();

// Web Vitals reporting to GTM dataLayer
(function() {
    var script = document.createElement('script');
    script.src = 'https://unpkg.com/web-vitals@3/dist/web-vitals.iife.js';
    script.onload = function() {
        function sendToGTM(metric) {
            if (window.dataLayer) {
                window.dataLayer.push({
                    event: 'web_vitals',
                    metric_name: metric.name,
                    metric_value: Math.round(metric.name === 'CLS' ? metric.value * 1000 : metric.value),
                    metric_rating: metric.rating,
                    metric_id: metric.id
                });
            }
        }
        try {
            webVitals.onCLS(sendToGTM);
            webVitals.onLCP(sendToGTM);
            webVitals.onINP(sendToGTM);
            webVitals.onFCP(sendToGTM);
            webVitals.onTTFB(sendToGTM);
        } catch(e) {}
    };
    document.head.appendChild(script);
})();
