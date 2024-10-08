
import { getCookie } from './cookie.js';
// call endpoint to get user info
function getUserInfo() {
    let token = getCookie('token');

    fetch('http://localhost/TWProject/backend/users/id' , {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ' + token
        }
    }
    ).then(response => {
        if(response.ok){
            // get the firstname and lastname of the user
            response.json().then(data => {
                document.getElementById('first_name').innerHTML = data.first_name;
                document.getElementById('last_name').innerHTML = data.last_name;
            } );
        } else if(response.status === 401) {
            // redirect to login page
            window.location.href = "http://localhost/TWProject/frontend/html/login.html";
        }
    }).catch(error => {
        console.log('Error getting user info');
    });
}

// call the function when the page loads
document.addEventListener('DOMContentLoaded', getUserInfo);
