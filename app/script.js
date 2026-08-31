document.getElementById("regForm").addEventListener("submit", function (e) {
  e.preventDefault();

  var form = e.target;
  var params = new URLSearchParams();
  params.append("name", form.name.value);
  params.append("email", form.email.value);
  params.append("phone", form.phone.value);
  params.append("workshop_id", form.workshop_id.value);
  params.append("seats", form.seats.value);

  var msg = document.getElementById("message");
  msg.textContent = "";
  msg.className = "";

  var xhr = new XMLHttpRequest();
  xhr.open("POST", "submit.php", true);
  xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

 xhr.onreadystatechange = function () {
  if (xhr.readyState !== 4) {
    return;
  }

  if (xhr.status === 200) {
    try {
      var data = JSON.parse(xhr.responseText);

      if (data.success) {
        msg.textContent = data.message;
        msg.className = "success";
        form.reset();
      } else {
        if (data.message) {
          msg.textContent = data.message;
        } else if (data.errors) {
          msg.textContent = data.errors.join(", ");
        } else {
          msg.textContent = "Something went wrong. Please try again.";
        }

        msg.className = "error";
      }
    } catch (error) {
      msg.textContent = "Invalid response from server.";
      msg.className = "error";
    }
  } else {
    msg.textContent = "Server error. Please try again.";
    msg.className = "error";
  }
};

  xhr.send(params.toString());
});
