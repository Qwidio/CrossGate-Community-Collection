fetch('slide.php')
.then(response => response.json())
.then(banners => {
    const slidesContainer = document.querySelector('#slides');
    banners.forEach(banner => {
        const slide = document.createElement('div');
        slide.className = 'posr autoMg wh100p flex acjc';
        slide.innerHTML = `
        <img src="Library/libsImg/${banner.bannerRefImg}"  class="posr autoMg w100 h100p containfit" alt="">
        <a href="Library/core/view.php?type=prms&ids=${banner.prmsIds}" class="link-cover" target="_blank" rel="noopener noreferrer">.</a>
        `;
        slidesContainer.appendChild(slide);
    });

    const slides = document.querySelector('#slides');
    let index = 0;
    const totalSlides = slides.children.length;
    const autoSlideInterval = 10000;

    function showSlide(n) {
        if (n >= totalSlides) index = 0;
        if (n < 0) index = totalSlides - 1;
        slides.style.transform = `translateX(${-index * 100}%)`;
    }

    function nextSlide() {
        showSlide(++index);
    }
    function prevSlide() {
        showSlide(--index);
    }

    document.querySelector('.prev').addEventListener('click', function() {
        prevSlide();
        resetAutoSlide();
    });
    document.querySelector('.next').addEventListener('click', function() {
        nextSlide();
        resetAutoSlide();
    });

    let autoSlideTimer = setInterval(nextSlide, autoSlideInterval);
    function resetAutoSlide() {
        clearInterval(autoSlideTimer);
        autoSlideTimer = setInterval(nextSlide, autoSlideInterval);
    }

    showSlide(index);
});
