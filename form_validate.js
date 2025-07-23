document.addEventListener("DOMContentLoaded", function() {
    document.getElementById('mainForm').addEventListener('submit', function(e) {
        let errors = [];
        let name = this.elements['name'].value.trim();
        let email = this.elements['email'].value.trim();
        let genderChecked = this.querySelector('input[name="gender"]:checked');
        let checkedHobbies = this.querySelectorAll('input[name="hobbies[]"]:checked');
        let country = this.elements['country'].value;

        if (name === "") errors.push("Name is required (JS).");
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(email)) errors.push("Valid email required (JS).");
        if (!genderChecked) errors.push("Select a gender (JS).");
        if (checkedHobbies.length === 0) errors.push("Select at least one hobby (JS).");
        if (country === "") errors.push("Select a country (JS).");

        if (errors.length > 0) {
            alert(errors.join("\n"));
            e.preventDefault();
        }
    });
});
