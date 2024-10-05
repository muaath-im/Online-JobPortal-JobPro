// AJAX function for CRUD operations
function ajaxRequest(url, method, data, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open(method, url, true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (xhr.status === 200) {
            callback(null, xhr.responseText);
        } else {
            callback(xhr.status);
        }
    };
    xhr.send(data);
}

// Form validation
function validateForm(formId) {
    // TODO
}



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

