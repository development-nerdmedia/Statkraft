import Swiper from 'swiper'
import SwiperCore, { Pagination, Autoplay, Navigation, Manipulation } from 'swiper/core'
import Swal from 'sweetalert2'
import Subscribe from '../common/Subscribe'
import VanillaTilt from 'vanilla-tilt'
import AOS from 'aos'
import axios from 'axios'
import moment from 'moment'
import { dataLayerHandler } from '../common/data-layer-handler'

SwiperCore.use([Pagination, Autoplay, Navigation, Manipulation]);

export default class HomePage {
  constructor () {
    this.endpoints = {
      base: 'https://api.conectaconelfuturo.pe',
      events: '/v1/events/get/list/0',
    }

    // Modal
    this.$modalContentVideo1 = document.querySelector('#ModalFirstVideo');
    this.$modalContentVideo2 = document.querySelector('#ModalSecondVideo');
    this.$modalSuscribe = document.querySelector('#ModalSuscribe');
    this.$modalSchedule = document.querySelector('#ModalSchedule');
    this.$modals = document.querySelectorAll('[data-modal]');

    // Slider
    this.$sliderCards = '#SliderCards';
    this.$sliderCardsPagination = '#SliderCardsPagination';

    // Slider Schedule
    this.$sliderSchedules = '#SliderSchedules';
    this.$sliderSchedulesPagination = '#SliderSchedulesPagination';

    // Form suscribe
    this.Subscribe = new Subscribe('#HomeFormSuscribe', '#HomeSectionSuscribeForm', '#HomeSectionSuscribeThanks');

    //DataLayer Envents
    this.$buttonBanner = document.querySelector('#ButtonBanner');
    this.$buttonPlay = document.querySelector('#ButtonPLay');
    this.$buttonBrochurePeople = document.querySelector('#ButtonBrochurePeople');
    this.$buttonBrochureCompany = document.querySelector('#ButtonBrochureCompany');
    this.$buttonOffer = document.querySelector('#ButtonOffer');
    this.$buttonTestimony = document.querySelector('#ButtonTestimony');
    this.$buttonRecycle = document.querySelector('#ButtonRecycle');
    // this.$buttonsSchedule = document.querySelectorAll('.button-schedule');
    this.$buttonBeforeSlideSchedule = document.querySelector('#ButtonBeforeSlideSchedule');
    this.$buttonNextSlideSchedule = document.querySelector('#ButtonNextSlideSchedule');
    this.$iconFace = document.querySelector('#IconFace');
    this.$iconTwitt = document.querySelector('#IconTwitt');
    this.$iconYT = document.querySelector('#IconYT');
    this.$iconLinkedin = document.querySelector('#IconLinkedin');
    this.$buttonRegister = document.querySelector('#ButtonRegister');

    this.events = null

    window.scrollTo(0, 0)
    this.init()
  }

  config () {
    axios.defaults.baseURL = this.endpoints.base
    moment.locale('es')
  }

  sliderTestimonials () {
    let config = {
      breakpoints: {
        768: {
          slidesPerView: 1,
          spaceBetween: 0,
          centeredSlides: false,
        },
        1050: {
          slidesPerView: 3,
          spaceBetween: 0,
          centeredSlides: false,
          loop: false
        },
      },
      pagination: {
        el: this.$sliderCardsPagination,
        clickable: true,
      },
    };

    const slider = new Swiper(this.$sliderCards, config);
  }

  async sliderSchedules () {
    let config = {
      slidesPerView: 1,
      spaceBetween: 20,
      loop: false,
      // breakpoints: {
      //   768: {
      //     slidesPerView: 2,
      //     spaceBetween: 0
      //   },
      //   1050: {
      //     slidesPerView: 3,
      //     spaceBetween: 0,
      //     // loop: false
      //   },
      // },
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
    };

    const sw = new Swiper(this.$sliderSchedules, config)

    sw.removeAllSlides()

    const { data } = await axios.get(this.endpoints.events)

    this.events = data.results

    const groups = data.results.reduce((groups, item) => {
      const { monthtext, year } = item;

      if (!groups[year]) {
        groups[year] = []
      }

      if (!groups[year][monthtext]) {
        groups[year][monthtext] = []
      }


      groups[year][monthtext].push(item);

      return groups;
    }, {});

    let initialSlide = 0;
    let initialSlideSelected = false;
    let totalSlides = 0;
    let todayDate = new Date();

    for (let elem in groups) {

      for (let mes in groups[elem]) {
        let slides = ''
        let thisMonthItems = 1;
        groups[elem][mes].forEach(month => {
          let date = new Date(month.EventDate.replace(/-/g, '/'));
          if(parseInt(month.year) == todayDate.getFullYear() && parseInt(month.month) == (todayDate.getMonth() + 1) && !initialSlideSelected) 
          {
              initialSlide = totalSlides;
              initialSlideSelected = true;
          }
          //const event = new Date("2021-09-16 00:00:00")
          //event.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' })

          slides += `
          <div class="schedule__date">
            <div class="schedule__day">
              <div class="schedule__day-text">${date.toLocaleDateString('es-ES', { weekday: 'long'})}</div>
              <div class="schedule__day-number">${date.toLocaleDateString('es-ES', { day: 'numeric'})}</div>
            </div>
            <div class="schedule__day-paragraph">${month.EventName}</div>
            <div class="schedule__hover hover-active">
              <a href="javascript:void(0);" data-modal="scheduleModal" data-id="${month.Id}" data-event="${month.EventName}" class="button-schedule"><span>VER</span> MÁS</a>
            </div>
          </div>`

          if(thisMonthItems % 4 == 0)
          {
            sw.appendSlide(`
            <div class="swiper-slide">
              <div class="schedule__body" data-aos="fade-up" data-aos-duration="1000">
                <div class="schedule__month">${mes}</div>
                <div class="schedule__date__content">
                ${slides}
                </div>
              </div>
            </div>
          `);
            slides = '';
            totalSlides++;
          }
          thisMonthItems++;
        });
        if(slides != '')
        {
          sw.appendSlide(`
          <div class="swiper-slide">
            <div class="schedule__body" data-aos="fade-up" data-aos-duration="1000">
              <div class="schedule__month">${mes}</div>
              <div class="schedule__date__content">
              ${slides}
              </div>
            </div>
          </div>
          `)
          totalSlides++;
        }        
      }
    }

    sw.slideTo(initialSlide);

    this.modalSchedule()

    // DataLayer
    let buttonsSchedule = document.querySelectorAll('.button-schedule');

    buttonsSchedule.forEach( button => {
      button.addEventListener('click', e => {
        let eventSchedule = e.target.dataset.event
        dataLayerHandler.sendDataAsync('Starkraft - Home','Seleccionar boton', `Agenda - Agosto 2021 - ${eventSchedule} - ver más`)
      })
    })
  }

  sectionFormSuscribe () {
    this.Subscribe.init()
  }

  effects () {
    // VanillaTilt
    VanillaTilt.init(document.querySelector('.header-stk__img-front'), {
      max: 15,
      speed: 200
    });

    // AOS
    AOS.init({
      debounceDelay: 50,
    })
  }

  modal () {

    const handleClickFirstVideoModal = () => {

      let content = this.$modalContentVideo1.innerHTML;
      let videoContent =  `<div style="padding:56.25% 0 0 0;position:relative;"><iframe src="https://www.youtube.com/embed/o9AG5PskrLU" title="YouTube video player" style="position:absolute;top:0;left:0;width:100%;height:100%;" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe></div>`
      let html = content.replace('_here_', videoContent);

      Swal.fire({
        html: html,
        showCloseButton: true,
        width: 1100,
        showConfirmButton:false,
        customClass: {
          container: 'video-black-background',
          popup: 'first-video-popup'
        },
        returnFocus: false
      })
    }

    const handleClickSecondVideoModal = () => {
      let content = this.$modalContentVideo2.innerHTML;
      let videoContent =  `
        <iframe width="560" height="315" src="https://www.youtube.com/embed/cUWrHxYT8-w" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
      `
      let html = content.replace('_here_', videoContent);

      Swal.fire({
        html: html,
        showCloseButton: true,
        width: 1100,
        showConfirmButton:false,
        customClass: {
          popup: 'second-video-popup'
        },
        returnFocus: false
      })
    }

    const handleClickThirdVideoModal = () => {
      let content = this.$modalContentVideo2.innerHTML;
      let videoContent =  `
        <iframe width="560" height="315" src="https://www.youtube.com/embed/qlFBFvtqk6U" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
      `
      let html = content.replace('_here_', videoContent);

      Swal.fire({
        html: html,
        showCloseButton: true,
        width: 1100,
        showConfirmButton:false,
        customClass: {
          popup: 'second-video-popup'
        },
        returnFocus: false
      })
    }

    const handleClickFourthVideoModal = () => {
      let content = this.$modalContentVideo2.innerHTML;
      let videoContent =  `
        <iframe width="560" height="315" src="https://www.youtube.com/embed/KeEjCL-AmLU" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
      `
      let html = content.replace('_here_', videoContent);

      Swal.fire({
        html: html,
        showCloseButton: true,
        width: 1100,
        showConfirmButton:false,
        customClass: {
          popup: 'second-video-popup'
        },
        returnFocus: false
      })
    }

    this.$modals.forEach(elem => {
      switch (elem.dataset.modal) {
        case 'firstVideoModal' :
          elem.addEventListener('click', handleClickFirstVideoModal)
        break

        case 'secondVideoModal' :
          elem.addEventListener('click', handleClickSecondVideoModal)
        break

        case 'thirdVideoModal' :
          elem.addEventListener('click', handleClickThirdVideoModal)
        break

        case 'fourthVideoModal' :
          elem.addEventListener('click', handleClickFourthVideoModal);
        break;
      }
    })
  }

  modalSchedule () {
    const handleClickScheduleModal = ({ target }) => {
      const event = this.events.find(el => el.Id === parseInt(target.dataset.id))
      const date = new Date(event.EventDate.replace(/-/g, '/'));

      let content = this.$modalSchedule.innerHTML;
      let scheduleContent =  `
        <div class="schedule-modal-content__month">Setiembre</div>
        <div class="schedule-modal-content__info">
        <div class="schedule-modal-content__day">
          <span>${date.toLocaleDateString('es-ES', { weekday: 'long'})}</span>
          <span>${date.toLocaleDateString('es-ES', { day: 'numeric'})}</span>
        </div>

        <div class="schedule-modal-content__title">${event.EventName}</div>
        <div class="schedule-modal-content__message">${event.Description}</div>
        <div class="schedule-modal-content__img">
          <img src="https://conectaconelfuturo.pe/storage/eventos/${event.EventImage}" alt="${event.EventName}">
        </div>
      </div>
      `
      let html = content.replace('_here_', scheduleContent);

      Swal.fire({
        html: html,
        showCloseButton: true,
        width: 800,
        showConfirmButton:false,
        customClass: {
          container: 'video-black-background-2',
          popup: 'schedule-video-popup',
          closeButton: 'agenda-close'
        },
        returnFocus: false
      })
    }

    document.querySelectorAll('[data-modal]').forEach(elem => {
      if (elem.dataset.modal === 'scheduleModal') {
        elem.addEventListener('click', handleClickScheduleModal)
      }
    })
  }


  init () {
    this.config()
    this.effects()
    this.sectionFormSuscribe()
    this.sliderTestimonials()
    this.sliderSchedules()
    this.modal()
  }
}
