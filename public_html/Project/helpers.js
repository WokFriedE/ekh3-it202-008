function flash(message = "", color = "info") {
    let flash = document.getElementById("flash");
    //create a div (or whatever wrapper we want)
    let outerDiv = document.createElement("div");
    outerDiv.className = "row justify-content-center";
    let innerDiv = document.createElement("div");

    //apply the CSS (these are bootstrap classes which we'll learn later)
    innerDiv.className = `alert alert-${color}`;
    //set the content
    innerDiv.innerText = message;

    outerDiv.appendChild(innerDiv);
    //add the element to the DOM (if we don't it merely exists in memory)
    flash.appendChild(outerDiv);
}

function flashClear() {
    let flash = document.getElementById("flash");
    for (item of flash.getElementsByTagName("div")) {
        flash.removeChild(item);
    }
}

function verifyUsername(form) {
    let user = form.username.value;
    let userPattern = /^[a-z0-9_-]{3,16}$/;

    // Username verification
    if (!(userPattern.test(user)) || user == "") {
        flash("Username must only contain 3-30 characters a-z, 0-9, _, or -", "warning");
        return false;
    }
    return true;
}

function verifyEmail(form) {
    let email = form.email.value;
    let emailPattern = /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/;

    // email verification
    if (!(emailPattern.test(email)) || email == "") {
        flash("Email is not valid", "warning")
        return false;
    }
    return true;
}

function comparePass(pw, con) {
    if (pw != con) {
        flash("Password and Confirm password must match", "warning");
        return false;
    }
    return false;
}