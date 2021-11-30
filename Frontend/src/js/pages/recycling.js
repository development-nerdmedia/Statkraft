import axios from 'axios'
import paginate from 'jw-paginate'
import AOS from 'aos'
import Subscribe from '../common/Subscribe'
import { dataLayerHandler } from '../common/data-layer-handler'

export default class RecyclingPage {
  constructor (router) {
    this.endpoints = {
      base: 'https://api.conectaconelfuturo.pe',
      recycling_places: (page, recordsperpage, departament, city, district) => `/v1/recyclepoints/get/list/${page}/${recordsperpage}/${departament}/${city}/${district}`,
      ubigeo: (type) => `/v1/ubigeo/getbyparentid/${type}`
    }

    // Form suscribe
    this.Subscribe = new Subscribe('#RecyclingFormSuscribe', '#RecyclingSectionSuscribeForm', '#RecyclingSectionSuscribeThanks', 'Recicla')

    // Filters
    this.$filterDepartament = document.querySelector(`#RecyclingFiltersDepartamento`)
    this.$filterCity = document.querySelector(`#RecyclingFiltersCiudad`)
    this.$filterDistrict = document.querySelector(`#RecyclingFiltersDistrito`)

    // Grid
    this.$results = document.querySelector(`#RecyclingResults`)

    // Pagination
    this.$pagination = document.querySelector(`#RecylingPagination`)

    // Message
    this.$message = document.querySelector(`#RecyclingMessage`)

    // Pages
    this.resultsPerPage = 6;
    this.page = 1;
    this.totalResults = 0;

    // Router
    this.router = router

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
  }

  sectionSubscribe () {
    this.Subscribe.init()
  }

  clearSelectFilter (select, defa) {
    let opt = document.createElement('option')
    opt.selected = true
    opt.innerHTML = defa
    opt.value = 0

    let i, l = select.options.length - 1

    for (i = l; i >= 0; i--) {
      select.remove(i)
    }

    select.appendChild(opt)
  }

  async fillUbigeo (select, type) {
    const getData = async (type) => {
      try {
        const { data } = await axios.get(this.endpoints.ubigeo(type))

        return data.ubigeos
      } catch (e) {
        console.error(e)
      }
    }

    const fillSelect = (select, data) => {
      data.forEach( ({ Name, Id }) => {
        let opt = document.createElement('option')
        opt.innerHTML = Name
        opt.value = Id

        select.appendChild(opt)
      });

      // Reset pagination
      this.page = 1
      this.totalResults = 0
    }

    fillSelect(select, await getData(type))
  }

  handleDepartament (type) {
    this.clearSelectFilter(this.$filterCity, 'Ciudad')
    this.clearSelectFilter(this.$filterDistrict, 'Distrito')
    this.fillUbigeo(this.$filterCity, type)

    this.search()
  }

  handleCity (type) {
    this.clearSelectFilter(this.$filterDistrict, 'Distrito')
    this.fillUbigeo(this.$filterDistrict, type)

    this.search()
  }

  handleDistrict (type) {
    this.search()
  }

  handlerFilters () {
    // First fill
    this.fillUbigeo(this.$filterDepartament, 0)

    const _self = this;

    if (!this.$filterDepartament.classList.contains('v')) {
      this.$filterDepartament.addEventListener('change', ({ target }) => {
        _self.handleDepartament(target.value)

        // Data Layer
        let departamentText = (_self.$filterDepartament.options[_self.$filterDepartament.selectedIndex].text)

        dataLayerHandler.sendDataAsync('Starkraft - Recicla','Visualizar contenido - Acordeon informativos', `Departamento - ${departamentText}`)

      });

      this.$filterDepartament.classList.add('v')

      this.handleDepartament(0) // Lima
    }

    if (!this.$filterCity.classList.contains('v')) {
      this.$filterCity.addEventListener('change', ({ target }) => {
        _self.handleCity(target.value)

        // Data Layer
        let cityText = (_self.$filterCity.options[_self.$filterCity.selectedIndex].text);
        dataLayerHandler.sendDataAsync('Starkraft - Recicla','Visualizar contenido - Acordeon informativos', `Ciudad - ${cityText}`)
      });

      this.$filterCity.classList.add('v')
    }

    if (!this.$filterDistrict.classList.contains('v')) {
      this.$filterDistrict.addEventListener('change', ({ target }) => {
        _self.handleDistrict(target.value)

        // Data Layer
        let districtText = (_self.$filterDistrict.options[_self.$filterDistrict.selectedIndex].text)
        dataLayerHandler.sendDataAsync('Starkraft - Recicla','Visualizar contenido - Acordeon informativos', `Distrito - ${districtText}`)
      });
      this.$filterDistrict.classList.add('v')
    }
  }

  async search () {
    let valueDepartament = this.$filterDepartament.value
    let valueCity = this.$filterCity.value
    let valueDistrict = this.$filterDistrict.value

    // Clear results box
    this.$results.innerHTML = ''
    this.message('Cargando..')

    try {
      const { data } = await axios.get(this.endpoints.recycling_places(this.page, this.resultsPerPage, valueDepartament, valueCity, valueDistrict))

      if (data.IsError === true) throw new Error('Ocurrió algo, intente más tarde.');

      if (data.results.length === 0) return this.message('No se encontraron resultados.');
      this.message()

      this.paginated(paginate(data.total, this.page, this.resultsPerPage))
      this.paintResults(data)

    } catch (e) {
      console.error(e)
    }
  }

  defaultListenerPaginated () {
    let prev = this.$pagination.querySelector('.pprev')
    let next = this.$pagination.querySelector('.pnext')

    if (!prev.classList.contains('v')) {
      prev.addEventListener('click', () => {
        this.page--
        this.search()

        //Data Layer
        dataLayerHandler.sendDataAsync('Starkraft - Recicla','Seleccionar boton', 'Página Anterior')
      })

      prev.classList.add('v')

    }

    if (!next.classList.contains('v')) {
      next.addEventListener('click', () => {
        this.page++
        this.search()

        //Data Layer
        dataLayerHandler.sendDataAsync('Starkraft - Recicla','Seleccionar boton', 'Página Siguiente')
      })

      next.classList.add('v')

    }
  }

  paginated (logic) {
    this.totalResults = logic.totalItems

    let voidPages = this.$pagination.querySelector('.ppages')
    let prev = this.$pagination.querySelector('.pprev')
    let next = this.$pagination.querySelector('.pnext')

    // Clean pages
    voidPages.innerHTML = ''
    prev.classList.remove('active')
    next.classList.remove('active')

    if (logic.currentPage > 1 ) {
      prev.classList.add('active')
    }

    if (logic.currentPage < logic.totalPages ) {
      next.classList.add('active')
    }

    logic.pages.forEach(p => {
      let s = document.createElement('span')
      s.innerHTML = p
      if (logic.currentPage === p) s.classList.add('active')

      voidPages.appendChild(s)

      s.addEventListener('click', () => {
        this.moveToPage(p)

        // Data Layer
        let buttonNumber = s.innerText
        dataLayerHandler.sendDataAsync('Starkraft - Recicla','Seleccionar boton', `Página ${buttonNumber}`)
      })
    })
  }

  moveToPage (page) {
    this.page = page
    this.search()
  }

  paintResults ({ results }) {
    this.$results.innerHTML = ''

    const HTML_PLACE_ELEMENT = (data, timing = 1000) => {
      let { Id, PointName, Thumbnail, Departamento } = data

      let anchor = document.createElement('a')

      anchor.dataset.aos = 'fade-up'
      anchor.dataset.aosDuration = timing
      anchor.dataset.place = PointName
      anchor.classList.add('recycling__element')
      anchor.href = `/lugares-de-reciclaje/${Id}`

      anchor.insertAdjacentHTML('beforeend', `
        <div class="recycling__element-container">
          <div class="recycling__element-img">
            <img src="https://conectaconelfuturo.pe/storage/puntos/${Thumbnail}">
            <div class="tag-stk">${Departamento}</div>
          </div>
          <div class="recycling__element-title">${PointName}</div>
        </div>
      `)

      anchor.addEventListener('click', (evt) => {
        evt.preventDefault()
        evt.stopPropagation()

        dataLayerHandler.sendDataAsync('Starkraft - Recicla','Seleccionar boton', `${PointName}`)
        this.router.navigate(`/lugares-de-reciclaje/${Id}`)
      })

      return anchor
    }

    let timing = 600
    results.forEach(elem => {
      this.$results.insertAdjacentElement('beforeend', HTML_PLACE_ELEMENT(elem, timing))
      timing = timing+350
    })

    this.router.updatePageLinks()

    // DataLayer
  }

  message (msg = '') {
    this.$message.innerHTML = msg

    if (msg.length === 0) {
      this.$message.classList.remove('active')
    } else {
      this.$message.classList.add('active')
    }
  }

  async init () {
    this.config()
    this.effects()
    this.sectionSubscribe()
    this.handlerFilters()
    this.defaultListenerPaginated()
  }
}
