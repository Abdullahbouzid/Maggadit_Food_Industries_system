<script>
    // Heart toggle functionality
    document.querySelectorAll('.heart-icon').forEach(heart => {
        heart.addEventListener('click', function(e) {
            e.preventDefault();
            const isLiked = this.classList.contains('fas');
            if (isLiked) {
                this.classList.remove('fas', 'liked');
                this.classList.add('far');
                this.setAttribute('data-liked', 'false');
            } else {
                this.classList.remove('far');
                this.classList.add('fas', 'liked');
                this.setAttribute('data-liked', 'true');
            }
        });
    });

    // Bottom nav active state
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // Language switcher (temporary client-side, but uses backend locale)
    const langSwitch = document.getElementById('lang-switch');
    if (langSwitch) {
        langSwitch.addEventListener('click', function() {
            let currentUrl = new URL(window.location.href);
            let currentLang = currentUrl.searchParams.get('lang') || '{{ app()->getLocale() }}';
            let newLang = currentLang === 'ar' ? 'en' : 'ar';
            currentUrl.searchParams.set('lang', newLang);
            window.location.href = currentUrl.toString();
        });
    }


    
</script>