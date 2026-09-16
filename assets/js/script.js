// Custom JavaScript for KoKCS OQS

document.addEventListener("DOMContentLoaded", function() {
    // 1. Animated Number Counters
    const animateValue = (obj, start, end, duration) => {
        let startTimestamp = null;
        const step = (timestamp) => {
            if (!startTimestamp) startTimestamp = timestamp;
            const progress = Math.min((timestamp - startTimestamp) / duration, 1);
            obj.innerHTML = Math.floor(progress * (end - start) + start);
            if (progress < 1) {
                window.requestAnimationFrame(step);
            }
        };
        window.requestAnimationFrame(step);
    };

    // Find all number elements and animate them
    const counters = document.querySelectorAll("h2.display-6, h3.fw-bold");
    counters.forEach(counter => {
        const target = parseInt(counter.innerText);
        if (!isNaN(target) && target > 0) {
            counter.innerText = "0";
            animateValue(counter, 0, target, 1500);
        }
    });

    // 2. Global SweetAlert Confirmations for Forms
    const confirmForms = document.querySelectorAll("form[onsubmit*='return confirm']");
    confirmForms.forEach(form => {
        const onsubmitText = form.getAttribute("onsubmit");
        const match = onsubmitText.match(/confirm\('([^']+)'\)/);
        const message = match ? match[1] : "Are you sure you want to proceed?";
        
        form.removeAttribute("onsubmit");
        
        form.addEventListener("submit", function(e) {
            e.preventDefault();
            Swal.fire({
                title: "Are you sure?",
                text: message,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#4f46e5",
                cancelButtonColor: "#64748b",
                confirmButtonText: "Yes, proceed!",
                customClass: {
                    confirmButton: "fw-semibold",
                    cancelButton: "fw-semibold"
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
    
    // 3. Transform PHP success/error alerts into Toast notifications
    const alerts = document.querySelectorAll(".alert.alert-dismissible");
    alerts.forEach(alert => {
        const isError = alert.classList.contains("alert-danger");
        const isWarning = alert.classList.contains("alert-warning");
        const isInfo = alert.classList.contains("alert-info");
        const text = alert.innerText.trim();
        
        alert.style.display = "none";
        
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener("mouseenter", Swal.stopTimer);
                toast.addEventListener("mouseleave", Swal.resumeTimer);
            }
        });

        let icon = "success";
        if (isError) icon = "error";
        else if (isWarning) icon = "warning";
        else if (isInfo) icon = "info";

        Toast.fire({
            icon: icon,
            title: text
        });
    });

    // 4. Dark Theme Toggle
    const themeToggleBtn = document.getElementById("themeToggle");
    const themeIcon = document.getElementById("themeIcon");

    // Function to set theme
    const setTheme = (theme) => {
        document.documentElement.setAttribute("data-theme", theme);
        localStorage.setItem("theme", theme);
        
        if (themeIcon) {
            if (theme === "dark") {
                themeIcon.classList.remove("bi-moon-stars-fill");
                themeIcon.classList.add("bi-sun-fill");
            } else {
                themeIcon.classList.remove("bi-sun-fill");
                themeIcon.classList.add("bi-moon-stars-fill");
            }
        }
    };

    // Initialize theme from localStorage
    const savedTheme = localStorage.getItem("theme") || "light";
    setTheme(savedTheme);

    if (themeToggleBtn) {
        themeToggleBtn.addEventListener("click", () => {
            const currentTheme = document.documentElement.getAttribute("data-theme");
            const newTheme = currentTheme === "dark" ? "light" : "dark";
            setTheme(newTheme);
        });
    }

    // 5. Fix Modal Stacking Context Issues
    const modals = document.querySelectorAll(".modal");
    modals.forEach(modal => {
        document.body.appendChild(modal);
    });

    // 6. Add smooth scroll behavior
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener("click", function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute("href"));
            if (target) {
                target.scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });
            }
        });
    });
});