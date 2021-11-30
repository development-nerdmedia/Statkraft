import { dataLayerHandler } from './data-layer-handler'

export default class DataMarkings {
  constructor () {
    if (typeof window.dataLayer !== 'object' && typeof window.dataLayerCCF !== 'object') {
      return;
    }

    this.init ()
  }

  dataLayersHome () {
    //DataLayer Envents
    let $buttonBanner = document.querySelector('#ButtonBanner');
    let $buttonPlay = document.querySelector('#ButtonPLay');
    let $buttonBrochurePeople = document.querySelector('#ButtonBrochurePeople');
    let $buttonBrochureCompany = document.querySelector('#ButtonBrochureCompany');
    let $buttonOffer = document.querySelector('#ButtonOffer');
    let $buttonTestimony = document.querySelector('#ButtonTestimony');
    let $buttonTestimonyTwo = document.querySelector('#ButtonTestimonyTwo');
    let $buttonTestimonyThree = document.querySelector('#ButtonTestimonyThree');
    let $buttonRecycle = document.querySelector('#ButtonRecycle');
    // let $buttonsSchedule = document.querySelectorAll('.button-schedule');
    let $buttonBeforeSlideSchedule = document.querySelector('#ButtonBeforeSlideSchedule');
    let $buttonNextSlideSchedule = document.querySelector('#ButtonNextSlideSchedule');
    let $iconFace = document.querySelector('#IconFace');
    let $iconTwitt = document.querySelector('#IconTwitt');
    let $iconYT = document.querySelector('#IconYT');
    let $iconLinkedin = document.querySelector('#IconLinkedin');
    let $buttonRegister = document.querySelector('#ButtonRegister');

    $buttonBanner.addEventListener("click", () => dataLayerHandler.sendData('Starkraft - Home','Seleccionar boton', 'Banner - Mira el video'));

    $buttonPlay.addEventListener("click", () => dataLayerHandler.sendData('Starkraft - Home','Seleccionar boton', 'Play video - Menos más'));

    $buttonBrochurePeople.addEventListener("click", () => dataLayerHandler.sendData('Starkraft - Home','Seleccionar boton', 'PDF - Manual del ciudadano responsable'));

    $buttonBrochureCompany.addEventListener("click", () => dataLayerHandler.sendData('Starkraft - Home','Seleccionar boton', 'PDF - Manual de la empresa responsable'));

    $buttonOffer.addEventListener("click", () => dataLayerHandler.sendData('Starkraft - Home','Seleccionar boton', 'Starkraft Perú - Ver ofertas'));

    $buttonTestimony.addEventListener("click", () => dataLayerHandler.sendData('Starkraft - Home','Seleccionar boton', 'Play video - Testimonio - Aníbal'));

    $buttonTestimonyTwo.addEventListener("click", () => dataLayerHandler.sendData('Starkraft - Home','Seleccionar boton', 'Play video - Testimonio - Vanessa'));

    $buttonTestimonyThree.addEventListener("click", () => dataLayerHandler.sendData('Starkraft - Home','Seleccionar boton', 'Play video - Testimonio - Pedro'));

    $buttonRecycle.addEventListener("click", () => dataLayerHandler.sendData('Starkraft - Home','Seleccionar boton', 'Conoce los puntos más cercanos - Ver más'));

    $buttonBeforeSlideSchedule.addEventListener("click", () => dataLayerHandler.sendData('Starkraft - Home','Seleccionar boton', 'Agenda - Agosto 2021 - anterior'));

    $buttonNextSlideSchedule.addEventListener("click", () => dataLayerHandler.sendData('Starkraft - Home','Seleccionar boton', 'Agenda - Agosto 2021 - siguiente'));

    $iconFace.addEventListener("click", () => dataLayerHandler.sendData('Starkraft - Redes Sociales','Seleccionar boton', 'Redes sociales - Facebook'));

    $iconTwitt.addEventListener("click", () => dataLayerHandler.sendData('Starkraft - Redes Sociales','Seleccionar boton', 'Redes sociales - Twitter'));

    $iconYT.addEventListener("click", () => dataLayerHandler.sendData('Starkraft - Redes Sociales','Seleccionar boton', 'Redes sociales - Youtube'));

    $iconLinkedin.addEventListener("click", () => dataLayerHandler.sendData('Starkraft - Redes Sociales','Seleccionar boton', 'Redes sociales - Linkedin'));

    $buttonRegister.addEventListener("click", () => dataLayerHandler.sendData('Starkraft - Home','Intención de registro', 'Formulario - enviar'));
  }

  dataLayerReciclyng () {
    // DataLayer
    let $buttonsPrevious = document.querySelectorAll('#RecyclingBack')
    let $buttonRegisterRecycle = document.querySelector('#ButtonRegisterRecycle')

    $buttonsPrevious.forEach( button => {
      button.addEventListener('click', () => dataLayerHandler.sendData('Starkraft - Recicla','Seleccionar boton', 'Regresar al home - atrás'))
    })

    $buttonRegisterRecycle.addEventListener("click", () => dataLayerHandler.sendData('Starkraft - Recicla','Intención de registro', 'Formulario - enviar'));
  }

  dataLayerReciclyngDetail () {
    const back = document.querySelector('#RecyclingDetailBack')

    back.addEventListener('click', () => {
      let place = document.querySelector('.recycling-detail__title').textContent
      dataLayerHandler.sendData(`Starkraft - Recicla - ${ place }`, 'Seleccionar boton', 'Regresar a Recicla - atrás')
    })
  }

  init ()  {
    this.dataLayersHome()
    this.dataLayerReciclyng()
    this.dataLayerReciclyngDetail()
  }
}
