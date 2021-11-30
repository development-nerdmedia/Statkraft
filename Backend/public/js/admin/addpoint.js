document.querySelector("#departamento").addEventListener("change", (event) => {
    departamentoId = event.target.value;
    if(departamentoId != "")
    {
        fetch(apiBaseUrl + '/v1/ubigeo/getbyparentid/' + departamentoId)
        .then(function(response) {
            return response.json();
        })
        .then(function(myJson) {
            provincias = document.querySelector("#provincia");            
            provincias.innerHTML = `<option value="">Seleccione una provincia</option>`;
            myJson.ubigeos.forEach(element => {
                provincias.innerHTML += `<option value="` + element.Id + `">` + element.Name + `</option>`;
            });
            document.querySelector("#ubigeoid").innerHTML = `<option value="">Seleccione un distrito</option>`;
        });
    }
});

document.querySelector("#provincia").addEventListener("change", (event) => {
    provinciaId = event.target.value;
    if(provinciaId != "")
    {
        fetch(apiBaseUrl + '/v1/ubigeo/getbyparentid/' + provinciaId)
        .then(function(response) {
            return response.json();
        })
        .then(function(myJson) {
            ubigeos = document.querySelector("#ubigeoid");
            ubigeos.innerHTML = `<option value="">Seleccione un distrito</option>`;
            myJson.ubigeos.forEach(element => {
                ubigeos.innerHTML += `<option value="` + element.Id + `">` + element.Name + `</option>`;
            });
        });
    }
});