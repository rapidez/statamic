<script>
    window.responsiveResizeObserver = new ResizeObserver((entries) => {
        entries.forEach(entry => {
            const imgWidth = entry.target.getBoundingClientRect().width;
            const multiplier = entry.target.dataset?.sharpen ? {{ config('rapidez-statamic.responsive_image_multiplier', 150) }} : 100;
            entry.target.parentNode.querySelectorAll('source').forEach((source) => {
                source.sizes = Math.ceil(imgWidth / window.innerWidth * multiplier) + 'vw';
            });
        });
    });
</script>
