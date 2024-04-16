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

// ekh3 - 4/1/24

function verifyUsername(user) {
    let userPattern = /^[a-z0-9_-]{3,16}$/;

    // Username verification
    if (!(userPattern.test(user)) || user == "") {
        flash("[Client] Username must only contain 3-30 characters a-z, 0-9, _, or -", "warning");
        return false;
    }
    return true;
}

function verifyEmail(email) {
    let emailPattern = /^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/;

    // email verification
    if (!(emailPattern.test(email)) || email == "") {
        flash("[Client] Email is not valid", "warning")
        return false;
    }
    return true;
}

function comparePass(pw, con) {

    if (pw != con) {
        flash("[Client] Password and Confirm password must match", "warning");
        return false;
    }
    return false;
}

function verifyPassword(pw) {
    let passwdPattern = /^(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;
    if (!passwdPattern.test(pw)) {
        flash("[Client] Password must be 8 characters, have 1 lowercase, 1 number, and 1 special character",
            "warning");
        return false;
    }
    return true;
}

function verifyScore(score) {
    let pattern = /^\d{1,3}(\.\d+)?$/;
    if (!(pattern.test(score)) || score == "") {
        flash("[Client] Invalid score please enter a number", "warning")
        return false;
    }
    return true;
}
