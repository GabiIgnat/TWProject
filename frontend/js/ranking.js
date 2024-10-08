// Definirea funcției getCookie
    function getCookie(name) {
        let cookies = document.cookie.split("; ");
        for (let i = 0; i < cookies.length; i++) {
            let cookie = cookies[i].split("=");
            if (cookie[0] === name) {
                return cookie[1];
            }
        }
        return "";
    }

// Utilizarea funcției getCookie în cadrul evenimentului "DOMContentLoaded"
    document.addEventListener("DOMContentLoaded", function() {
    let token = getCookie("token");
    if (token === "") {
        window.location.href = "http://localhost/TWProject/frontend/html/Home.html";
    }

    fetch("http://localhost/TWProject/backend/users/ranking", {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + token
        }
    })
        .then(function(response) {
            if (response.ok) {
                return response.json();
            }
            else if(response.status === 401){
                window.location.href = "http://localhost/TWProject/frontend/html/Login.html";
            }
            else {
                throw new Error("Error fetching data.");
            }
        })
        .then(function(data) {
            for (let i = 0; i < data.length; i++) {
                console.log(data[i].ranking);
                let position = data[i].ranking;
                let username = data[i].username;
                let score = data[i].points;
                let playingSince = data[i].created_at;
                playingSince= playingSince.substring(0, 11);
                let row = "<tr><td>" + position + "</td><td>" + username + "</td><td>" + score + "</td><td>" + playingSince + "</td></tr>";
                document.getElementById("table-body").innerHTML += row;
            }})
        .catch(function(error) {
            console.log(error);
        });
});
