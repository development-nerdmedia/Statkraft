import Pristine from 'pristinejs'
import axios from 'axios'
import { dataLayerHandler } from '../common/data-layer-handler'

export default class Subscribe {
  constructor (
    FORM_SUBSCRIBE,
    CONTAINER_INFO,
    CONTAINER_THANKS,
    SECTION = 'home'
  ) {
    this.endpoints = {
      suscribe: 'https://api.conectaconelfuturo.pe/v1/newsletter/save',
    }

    this.section = SECTION

    // Form suscribe
    this.$formSuscribe = document.querySelector(FORM_SUBSCRIBE)
    this.$SectionHomeFormSuscribeForm = document.querySelector(CONTAINER_INFO)
    this.$SectionHomeFormSuscribeThanks = document.querySelector(CONTAINER_THANKS)
    this.PristineConfig = {
      classTo: 'input-control',
      errorClass: 'has-danger',
      successClass: 'has-success',
      errorTextParent: 'input-control',
      errorTextTag: 'div',
      errorTextClass: 'text-help'
    }

    // if
    if (!this.$formSuscribe.classList.contains('v')) {
      this.$formSuscribe.classList.add('v')
    }else {
      return;
    }

    this.PristineFormSuscribe = new Pristine(this.$formSuscribe, this.PristineConfig)
  }

  form () {
    const regenerateForm = () => {
      setTimeout(() => {
        this.$SectionHomeFormSuscribeThanks.style.display = 'none'
        this.$SectionHomeFormSuscribeForm.style.display = 'flex'
      }, 5500)

      this.$formSuscribe.querySelector(`input[name="email"]`).value = ''
    }

    const error = (e) => {
      console.error(e)
      alert('Algo salió mal, intentelo más tarde.')
    }

    const suscribeEmail = async () => {
      try {
        let body = new FormData()
        let email = this.$formSuscribe.querySelector(`input[name="email"]`).value
        body.append('Email', email)
        const { data } = await axios.post(
          this.endpoints.suscribe,
          body,
          { 'Content-Type': 'multipart/form-data'}
        )

        if (data.IsError === false) {
          this.$SectionHomeFormSuscribeForm.style.display = 'none'
          this.$SectionHomeFormSuscribeThanks.style.display = 'block'

          regenerateForm()

          // DataLayer
          dataLayerHandler.sendData(`Starkraft - ${this.section}`, 'Registro exitoso', 'Formulario - enviar')
        } else {
          throw new Error("Algo malio sal")
        }
      } catch (error) {
        error(error)
      }
    }

    const validation = (e) => {
      e.preventDefault()

      if (this.PristineFormSuscribe.validate()) suscribeEmail()
    }

    this.$formSuscribe.addEventListener('submit', validation)
  }

  init () {
    this.form()
  }
}
