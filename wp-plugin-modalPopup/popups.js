// https://www.w3schools.com/js/js_cookies.asp
function setCookie(cname, cvalue, exdays) {
  var d = new Date();
  d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
  var expires = "expires="+d.toUTCString();
  document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
  var name = cname + "=";
  var ca = document.cookie.split(';');
  for(var i = 0; i < ca.length; i++) {
    var c = ca[i];
    while (c.charAt(0) == ' ') {
      c = c.substring(1);
    }
    if (c.indexOf(name) == 0) {
      return c.substring(name.length, c.length);
    }
  }
  return "";
}

function checkCookie(event) {
  let checked = event.target.checked
  let popup = getCookie("popup");

  if (checked == true) {
    if (popup == "") {
      // není zde kontrola contentu (může se změnit ze dne na den), takže je cookie platná pouze 1 den
      setCookie("popup", "disabled", 1)
    }
  } else {
    if (popup == "disabled") {
      document.cookie = "popup=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
    }
  }
}

function showPopup(title, description, location, link = null) {
  let body = document.getElementsByTagName("body")[0];
  let container = document.createElement("div");

  locationCss = locationCss(location);

  container.style.cssText = "position: fixed; top: 0; bottom: 0; left: 0; right: 0; z-index: 10000; opacity: 0.9;";

  link = ((link != null) ? link : "#");

  container.id = "popupMain";
  container.innerHTML = `
  <div style='${locationCss} background-color: grey; border-radius: 5px; min-height: min-content; color: white; word-break: break-all;'>
    <button style='float: right; margin: 0; padding: 5px 10px; background-color: black; color: white;' onclick='popupClose();'>x</button>
    <label style='float: right; padding: 5px 10px 5px 0; position: relative'>
      <input type='checkbox' onclick='checkCookie(event);'> Do not show again
    </label>
    <p style='text-align: center; font-size: 24px; font-weight: bold; padding-top: 40px;'>${title}<p>
    <a href='${link}'>
      <div>
        <p style='padding: 10px 40px; font-size: 18px;'>
          ${description}
        </p>
      </div>
    </a>
  </div>
  `

  body.appendChild(container);
}

function locationCss(location) {
  let result = "";
  switch (location) {
    case "top":
      result = "position: relative; top: 0; left: 0; right: 0;";
      break;
    case "bottom":
      result = "position: fixed; bottom: 0; left: 0; right: 0;";
      break;
    default:
      result = "position: relative; left: 50%; top: 50%; transform: translate(-50%, -50%); width: 50%; max-height: 80%;";
      break;
  }
  return(result);
}

function popupClose() {
  container = document.getElementById("popupMain");
  container.parentNode.removeChild(container);
}
