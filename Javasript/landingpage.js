// Simple JavaScript for interactive elements
document.addEventListener("DOMContentLoaded", function () {
  // Smooth scroll for anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      document.querySelector(this.getAttribute("href")).scrollIntoView({
        behavior: "smooth",
      });
    });
  });

  // Animate stats counter
  const statItems = document.querySelectorAll(".stat-item h3");
  const options = {
    threshold: 0.5,
  };

  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const target = +entry.target.innerText.replace(/[^0-9]/g, "");
        const suffix = entry.target.innerText.replace(/[0-9]/g, "");
        const duration = 2000;
        const start = 0;
        const increment = target / (duration / 16);

        let current = start;
        const timer = setInterval(() => {
          current += increment;
          if (current >= target) {
            clearInterval(timer);
            current = target;
          }
          entry.target.innerText = Math.floor(current) + suffix;
        }, 16);

        observer.unobserve(entry.target);
      }
    });
  }, options);

  statItems.forEach((item) => {
    observer.observe(item);
  });
});
