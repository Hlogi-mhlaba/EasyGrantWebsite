document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("bookingForm");
  const queueTable = document.getElementById("queueTable");

  // Set today's date as the minimum date selectable
  const today = new Date().toISOString().split('T')[0];
  document.getElementById("date").setAttribute("min", today);

  // Disallow past times when today's date is picked
  const dateInput = document.getElementById("date");
  const timeInput = document.getElementById("time");

  dateInput.addEventListener("change", function () {
    const selectedDate = dateInput.value;
    const now = new Date();
    const todayStr = now.toISOString().split('T')[0];

    if (selectedDate === todayStr) {
      // Get current time in HH:MM format
      let hours = now.getHours().toString().padStart(2, '0');
      let minutes = now.getMinutes().toString().padStart(2, '0');
      const currentTime = `${hours}:${minutes}`;
      timeInput.min = currentTime;
    } else {
      // Allow any time if a future date is picked
      timeInput.removeAttribute("min");
    }
  });

  //prevention of default submission
  form.addEventListener("submit", function (e) {
    e.preventDefault();

    const fullName = document.getElementById("fullname").value.trim();
    const idNumber = document.getElementById("idnumber").value.trim();
    const date = document.getElementById("date").value;
    const time = document.getElementById("time").value;
    const cellphone = document.getElementById("cellnumber").value.trim();

    // Check if fields are empty
    if (!fullName || !idNumber || !date || !time || !cellphone) {
      alert("⚠️ Please fill in all fields.");
      return;
    }
    

    // Only allow letters and spaces in full name
    const nameRegex = /^[A-Za-z\s]+$/;
    if (!nameRegex.test(fullName)) {
      alert("⚠️ Full name must contain letters and spaces only.");
      return;
    }

    // Validate cellphone number
    const cellphoneRegex = /^[0-9]{10}$/;
    if (!cellphoneRegex.test(cellphone)) {
      alert("📱 Please enter a valid 10-digit cellphone number.");
      return;
    }

    // Prevent booking past date/time
const selectedDateTime = new Date(`${date}T${time}`);
const now = new Date();
now.setSeconds(0);
now.setMilliseconds(0);

if (selectedDateTime < now) {
  alert("⏰ You cannot book a date or time that has already passed.");
  return;
}


    // Restrict time to between 08:00 and 16:00
    const [hour, minute] = time.split(":").map(Number);
    if (hour < 8 || hour > 16 || (hour === 16 && minute > 0)) {
      alert("⏳ Bookings are only allowed between 08:00 and 16:00.");
      return;
    }

    // Check for duplicate bookings in the table (same ID + date + time)
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

    // Generate a ticket number starting from 1 and incrementing
    const existingRows = queueTable.querySelectorAll("tr").length;
    const ticketNumber = "TCK-" + (existingRows + 1); // e.g. TCK-1, TCK-2...


    // Send booking to server (PHP + MySQL)
    const formData = new FormData();
    formData.append("fullname", fullName);
    formData.append("idnumber", idNumber);
    formData.append("date", date);
    formData.append("time", time);
    formData.append("ticketnumber", ticketNumber);

    fetch("save_booking.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `fullname=${encodeURIComponent(fullName)}&idnumber=${encodeURIComponent(idNumber)}&date=${encodeURIComponent(date)}&time=${encodeURIComponent(time)}&cellphone=${encodeURIComponent(cellphone)}&ticketnumber=${encodeURIComponent(ticketNumber)}`
    })
      .then(response => response.text())
      .then(result => {
        if (result === "duplicate") {
          alert("📛 This booking already exists in the database.");
        } else if (result === "success") {
          const newRow = document.createElement("tr");
          newRow.innerHTML = `
          <td>${fullName}</td>
          <td>${idNumber}</td>
          <td>${date}</td>
          <td>${time}</td>
          <td>${cellphone}</td>
          <td>${ticketNumber}</td>
        `;

          queueTable.appendChild(newRow);
          form.reset();
          form.classList.remove('was-validated');
       // ✅ Show success message + ticket number
  alert(`✅ Booking successful!\n🎟️ Your Ticket Number: ${ticketNumber}`);
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

// Bootstrap 5 client-side validation
(() => {
  'use strict';
  const forms = document.querySelectorAll('.needs-validation');
  Array.from(forms).forEach(form => {
    form.addEventListener('submit', event => {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    }, false);
  });
})();
