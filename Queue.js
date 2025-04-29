// Wait for the DOM to fully load before running the script
document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("bookingForm");        // Form element
  const queueTable = document.getElementById("queueTable");    // Table to display bookings

  // Set today's date as the minimum selectable date in the date input
  const today = new Date().toISOString().split('T')[0];
  document.getElementById("date").setAttribute("min", today); // Prevent past date selection

  const dateInput = document.getElementById("date");
  const timeInput = document.getElementById("time");

  // Ensure users can't select a past time when booking for today
  dateInput.addEventListener("change", function () {
      const selectedDate = dateInput.value;
      const now = new Date();
      const todayStr = now.toISOString().split('T')[0];

      if (selectedDate === todayStr) {
          // Set current time as minimum if booking for today
          let hours = now.getHours().toString().padStart(2, '0');
          let minutes = now.getMinutes().toString().padStart(2, '0');
          const currentTime = `${hours}:${minutes}`;
          timeInput.min = currentTime;
      } else {
          // No time restriction if future date
          timeInput.removeAttribute("min");
      }
  });

  // Handle form submission
  form.addEventListener("submit", function (e) {
      // Grab and trim form field values
      const fullName = document.getElementById("fullname").value.trim();
      const idNumber = document.getElementById("idnumber").value.trim();
      const date = document.getElementById("date").value;
      const time = document.getElementById("time").value;
      const cellphone = document.getElementById("cellnumber").value.trim();

      // Check if any field is empty
      if (!fullName || !idNumber || !date || !time || !cellphone) {
          alert("⚠️ Please fill in all fields.");
          return;
      }

      // Validate full name (only letters and spaces)
      const nameRegex = /^[A-Za-z\s]+$/;
      if (!nameRegex.test(fullName)) {
          alert("⚠️ Full name must contain letters and spaces only.");
          return;
      }

      // Validate cellphone number (10 digits)
      const cellphoneRegex = /^[0-9]{10}$/;
      if (!cellphoneRegex.test(cellphone)) {
          alert("📱 Please enter a valid 10-digit cellphone number.");
          return;
      }

      // Prevent bookings in the past
      const selectedDateTime = new Date(`${date}T${time}`);
      const now = new Date();
      now.setSeconds(0);
      now.setMilliseconds(0);

      if (selectedDateTime < now) {
          alert("⏰ You cannot book a date or time that has already passed.");
          return;
      }

      // Ensure booking is within working hours (08:00 - 16:00)
      const [hour, minute] = time.split(":").map(Number);
      if (hour < 8 || hour > 16 || (hour === 16 && minute > 0)) {
          alert("⏳ Bookings are only allowed between 08:00 and 16:00.");
          return;
      }

      // Check for duplicate booking (based on ID, date, time, and cellphone)
      const rows = queueTable.querySelectorAll("tr");
      let duplicate = false;

      rows.forEach(row => {
          const cells = row.querySelectorAll("td");
          const existingID = cells[1]?.innerText;
          const existingDate = cells[2]?.innerText;
          const existingTime = cells[3]?.innerText;
          const existingPhone = cells[4]?.innerText;

          if (
              existingID === idNumber &&
              existingDate === date &&
              existingTime === time &&
              existingPhone === cellphone
          ) {
              duplicate = true;
          }
      });

      if (duplicate) {
          alert("📛 This booking already exists. Please choose a different time.");
          return;
      }

      let ticketnumber = ""; // Let PHP generate the correct sequential number


      console.log("Generated Ticket Number:", ticketnumber); // Debug log

      // Prepare data for server-side processing (PHP + MySQL)
      const formData = new FormData();
      formData.append("fullname", fullName);
      formData.append("idnumber", idNumber);
      formData.append("date", date);
      formData.append("time", time);
      formData.append("cellphone", cellphone);
      //formData.append("ticketnumber", ticketnumber);

      // Debugging data sent
      console.log("Data being sent to server:", `fullname=${encodeURIComponent(fullName)}&idnumber=${encodeURIComponent(idNumber)}&date=${encodeURIComponent(date)}&time=${encodeURIComponent(time)}&cellphone=${encodeURIComponent(cellphone)}&ticketnumber=${encodeURIComponent(ticketnumber)}`);

      // Send form data to PHP backend via POST
      fetch("save_booking.php", {
          method: "POST",
          headers: {
              "Content-Type": "application/x-www-form-urlencoded"
          },
          body: `fullname=${encodeURIComponent(fullName)}&idnumber=${encodeURIComponent(idNumber)}&date=${encodeURIComponent(date)}&time=${encodeURIComponent(time)}&cellphone=${encodeURIComponent(cellphone)}`
        })
      .then(response => response.text())
      .then(result => {
        if (result.startsWith("duplicate")) {
            alert("📛 This booking already exists in the database.");
        } else if (result.startsWith("success")) {
            const parts = result.split("|");
            const ticketnumber = parts[1];

              // Add booking to table visually
              const newRow = document.createElement("tr");
              newRow.innerHTML = `
                  <td>${fullName}</td>
                  <td>${idNumber}</td>
                  <td>${date}</td>
                  <td>${time}</td>
                  <td>${cellphone}</td>
                  <td>${ticketnumber}</td>
              `;
              queueTable.appendChild(newRow);

              form.reset(); // Clear form fields
              form.classList.remove('was-validated'); // Reset form validation style

              // Show confirmation with ticket number
              alert(`✅ Booking successful!\n🎟️ Your Ticket Number: ${ticketnumber}`);
          } else {
              alert("❌ Something went wrong while saving. Please try again.");
          }
      })
      .catch(error => {
          console.error("Error:", error);
          alert("🚫 Could not connect to the server.");
      });
  });
});

// Optional: Bootstrap 5 form validation styling
(() => {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');

  Array.from(forms).forEach(form => {
      form.addEventListener('submit', event => {
          if (!form.checkValidity()) {
              event.preventDefault();  // Stop form if invalid
              event.stopPropagation(); // Prevent bubbling
          }
          form.classList.add('was-validated'); // Apply validation styling
      }, false);
  });
})();
