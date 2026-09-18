const slideDelays = [7900, 9000, 11000];

document.querySelectorAll('[data-slider]').forEach((slider, sliderIndex) => {
	const slides = slider.querySelectorAll('.gallery-slide');
	const dots = slider.querySelectorAll('.slider-dot');
	const slideDelay = slideDelays[sliderIndex] || 5000;
	let currentSlide = 0;
	let autoSlide;

	const showSlide = (slideIndex) => {
		currentSlide = (slideIndex + slides.length) % slides.length;
		slides.forEach((slide, index) => slide.classList.toggle('active', index === currentSlide));
		dots.forEach((dot, index) => dot.classList.toggle('active', index === currentSlide));
	};

	const startAutoSlide = () => {
		autoSlide = window.setInterval(() => showSlide(currentSlide + 1), slideDelay);
	};

	const restartAutoSlide = () => {
		window.clearInterval(autoSlide);
		startAutoSlide();
	};

	slider.querySelector('.previous').addEventListener('click', () => {
		showSlide(currentSlide - 1);
		restartAutoSlide();
	});
	slider.querySelector('.next').addEventListener('click', () => {
		showSlide(currentSlide + 1);
		restartAutoSlide();
	});
	dots.forEach((dot, index) => dot.addEventListener('click', () => {
		showSlide(index);
		restartAutoSlide();
	}));
	slider.addEventListener('mouseenter', () => window.clearInterval(autoSlide));
	slider.addEventListener('mouseleave', startAutoSlide);
	startAutoSlide();
});
