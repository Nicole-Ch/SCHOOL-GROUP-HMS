document.addEventListener("DOMContentLoaded", function () {
    const section = document.getElementById("socials");
    window.addEventListener("scroll", function () {
        if (section.getBoundingClientRect().top < window.innerHeight * 0.75) { //checks if the section is in view
            section.classList.add("visible"); //activates the CSS class "visible" when the section is in view
        }
    });
});

//DOMContentLoaded waits for the entire HTML document to be loaded  before executing the script function
// getElementById("socials") finds the HTML element with the ID "socials"
// addEventListener("scroll") Listens to see if the socials section is in view
//section.getBoundingClientRect().top - Returns the number of pixels between the top of the #socials section and the top of the viewport.
