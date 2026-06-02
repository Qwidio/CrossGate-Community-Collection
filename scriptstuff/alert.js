// this makes everything much easier to debug
document.addEventListener("DOMContentLoaded", function() {
    const borderAnimate = document.getElementById('borderanimate');
    setTimeout(() => {
        borderAnimate.style.animation = 'none'; // Removing animation
        void borderAnimate.offsetWidth; // Triggering reflow
        borderAnimate.style.animation = 'ReverseTimer'; // Adding back animation
    }, 500);
});
function alerter(content) {
    const alertcard = document.getElementById('alertcard');
    const alertcontent = document.getElementById('alertcontent');
    alertcontent.textContent = content;
    setTimeout(() => {
        alertcard.style.transform = "translateX(0)";
    }, 100);
    setTimeout(() => {
        alertcard.style.transform = "translateX(100vw)";
    }, 5000);
};
