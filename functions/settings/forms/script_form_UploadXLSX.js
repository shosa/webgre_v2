document.getElementById('file').addEventListener('change', function (event) {
    var fileName = event.target.files[0].name;
    var nextSibling = event.target.nextElementSibling;
    nextSibling.innerText = fileName;
});
document.getElementById('uploadForm').addEventListener('submit', function () {
    document.getElementById('loader').style.display = 'block';
});
