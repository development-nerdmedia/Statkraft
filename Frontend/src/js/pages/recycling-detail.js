import axios from 'axios'
import L from 'leaflet'
import icon from 'leaflet/dist/images/marker-icon.png';
import iconShadow from 'leaflet/dist/images/marker-shadow.png';
import AOS from 'aos'

var lmap;
var lmarker;

export default class RecyclingDetailPage {

  marker = null;

  constructor ( lugar, router ) {
    this.place = lugar
    this.router = router

    this.$RecyclingDetailContainer = document.querySelector('#RecyclingDetailContainer')

    this.endpoints = {
      base: 'https://api.conectaconelfuturo.pe',
      getPlace: (id) => `/v1/recyclepoints/get/byid/${id}`
    }

    // Map locations
    this.mapKey = 'pk.eyJ1Ijoid2V4aW5vaDE1NSIsImEiOiJja29haTZ1YW0xeGZzMnZvbmN1ZHloMTB5In0.B4RtLgHJX2tbl5MP57uCDA';
    this.mapEndpoint = `https://api.mapbox.com/styles/v1/{id}/tiles/{z}/{x}/{y}?access_token=${this.mapKey}`;
    if(lmap == null)
    {
      lmap = L.map('RecyclingDetailMap');
    }
    this.map = lmap;

    window.scrollTo(0, 0)
    this.init()
  }

  config () {
    axios.defaults.baseURL = this.endpoints.base
  }

  effects () {
    // AOS
    AOS.init({
      debounceDelay: 50,
    })

    this.$RecyclingDetailContainer.classList.remove('animate__animated')
    this.$RecyclingDetailContainer.classList.remove('animate__fadeInUp')
  }

  setMap ({
    name,
    longitude,
    latitude
  }) {
    let point = [latitude, longitude];
    let defaultIcon = L.icon({
      iconUrl: icon,
      iconRetinaUrl: icon,
      shadowUrl: iconShadow,
      shadowRetinaUrl: iconShadow,
      iconSize: [24,36],
      iconAnchor: [12,36]
    });

    // Title
    L.tileLayer(this.mapEndpoint, {
      maxZoom: 20,
      attribution: '',
      id: 'mapbox/streets-v11'
    }).addTo(this.map);

    // Marker
    this.map.setView(point, 17)

    if(lmarker != null)
    {
      lmarker.remove();
    }

    this.marker = L.marker(point, {
      icon: defaultIcon
    })
    .bindPopup(` ${name}`)
    .addTo(this.map)
    .openPopup();

    lmarker = this.marker;

  }

  async getPlace () {
    try {
      const { data } = await axios.get(this.endpoints.getPlace(this.place))

      if (data.IsError === true) throw new Error('Algo salio mal.');

      const { Id, PointName, Address, Telephone, Hours, Latitude, Longitude, Options } = data.results

      document.querySelector(`.rr-name`).innerHTML = PointName
      document.querySelector(`.rr-direction`).innerHTML = Address
      document.querySelector(`.rr-phone`).innerHTML = Telephone
      document.querySelector(`.rr-hour`).innerHTML = Hours

      this.setMap({
        name: PointName,
        longitude: Longitude,
        latitude: Latitude
      });

      document.querySelectorAll('.stbin').forEach(el => el.classList.add('.ghh'));

      Options.forEach(p => {
        document.querySelector(`.stbin-${p}`).classList.remove('ghh')
      });


      this.$RecyclingDetailContainer.classList.add('animate__animated')
      this.$RecyclingDetailContainer.classList.add('animate__fadeInUp')
    } catch (e) {
      console.error(e)
    }
  }

  init () {

    this.config()
    this.getPlace()
    this.router.updatePageLinks()
  }
}
