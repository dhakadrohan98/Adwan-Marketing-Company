function httpGetAsync(url) {

    var body = document.querySelector('#getTracking');
    var xmlHttp = new XMLHttpRequest();
    body.innerHTML = '<h2>Please wait until the information is received</h2>';
    xmlHttp.onreadystatechange = function () {
        if (xmlHttp.readyState == 4 && xmlHttp.status == 200) {
            body.innerHTML = '<h2>Tracking</h2>';
            var res = JSON.parse(xmlHttp.response);
            if (res['success'] === true) {
                var data = res['data'];
                for (let key in data) {
                    var val = data[key];
                    var div = document.createElement('div');
                    var b = document.createElement('b');
                    b.textContent = key + ' : ';
                    var span = document.createElement('span');
                    span.textContent = val;
                    div.append(b);
                    div.append(span);
                    body.append(div);
                }
            } else {
                body.innerHTML += '<h3>' + res['message'] + '</h3>';
            }
        }
    }
    xmlHttp.open("GET", url, true); // true for asynchronous
    xmlHttp.send(null);
}