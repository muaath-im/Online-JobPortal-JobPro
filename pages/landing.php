<?php 
session_start();
$pageTitle = "Landing Page";
$pagename = 'landing';
include '../includes/header.php';
?>

<section class="slider">
            <div class="container">
                <div class="slider-content">
                    <h2>JobPro</h2>
                    <h3 id="slider-title">First slide label</h3>
                    <p id="slider-description">Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
                    <div class="slider-dots">
                        <span class="active" onclick="changeSlide(0)"></span>
                        <span onclick="changeSlide(1)"></span>
                        <span onclick="changeSlide(2)"></span>
                    </div>
                </div>
            </div>
        </section>

        <section class="reviews">
            <div class="container">
                <h2>User reviews:</h2>
                <div class="review-grid">
                    <div class="review">
                        <div class="review-avatar"></div>
                        <div class="review-content">
                            <h3>Name1</h3>
                            <div class="stars">★★★★☆</div>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc maximus, nulla ut commodo sagittis, sapien dui mattis dui, non pulvinar lorem felis nec erat.</p>
                        </div>
                    </div>
                    <div class="review">
                        <div class="review-avatar"></div>
                        <div class="review-content">
                            <h3>Name2</h3>
                            <div class="stars">★★★★★</div>
                            <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nunc maximus, nulla ut commodo sagittis, sapien dui mattis dui, non pulvinar lorem felis nec erat.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <script>
            const slides = [
    { title: "First slide label", description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit." },
    { title: "Second slide label", description: "Praesent commodo cursus magna, vel scelerisque nisl consectetur." },
    { title: "Third slide label", description: "Nullam id dolor id nibh ultricies vehicula ut id elit." }
];

let currentSlide = 0;

function changeSlide(index) {
    currentSlide = index;
    updateSlider();
}

function updateSlider() {
    const title = document.getElementById('slider-title');
    const description = document.getElementById('slider-description');
    const dots = document.querySelectorAll('.slider-dots span');

    title.textContent = slides[currentSlide].title;
    description.textContent = slides[currentSlide].description;

    dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentSlide);
    });
}

// Auto-rotate slides
setInterval(() => {
    currentSlide = (currentSlide + 1) % slides.length;
    updateSlider();
}, 5000);
        </script>

<?php include '../includes/footer.php'; ?>