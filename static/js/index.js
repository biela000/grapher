const temperatureMap = document.querySelector('.temperature-map');

const fetchTemperatures = async () => {
    const response = await fetch('/temperature.php');
    const data = await response.json();

    temperatureMap.innerHTML = '';

    data.forEach(record => {
        temperatureMap.innerHTML += `
            <area shape="circle" coords="${record.label_pos},${record.value_pos},10" href="#" alt="temperature">
        `;
    });
}

fetchTemperatures();