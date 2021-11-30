import Navigo from 'navigo'
import HomePage from './pages/home'
import RecyclingPage from './pages/recycling'
import RecyclingDetailPage from './pages/recycling-detail'
import DataMarkings from './common/data-markings'
import './common/utils'

export default class Pages {
  constructor () {
    this.router = new Navigo('/', {
        hash: true,
        noMatchWarning: true
      }
    )

    this.init()
  }

  // https://github.com/krasimir/navigo/blob/master/DOCUMENTATION.md#matching-logic
  routes () {
    const _self = this

    let routes = {
      '/': {
        as: 'home',
        uses: () => {
          try {
            _self.ale('home')
            (new HomePage())
          } catch (e) {
            console.error(e)
          }
        }
      },
      '/lugares-de-reciclaje': {
        as: 'recycling',
        uses: () => {
          try {
            _self.ale('recycling')
            (new RecyclingPage(_self.router))
          } catch (e) {
            console.error(e)
          }
        }
      },
      '/lugares-de-reciclaje/:lugar': {
        as: 'recycling_detail',
        uses: ({data}) => {
          try {
            _self.ale('recycling_detail')
            (new RecyclingDetailPage(data.lugar, _self.router))
          } catch (e) {
            console.error(e)
          }
        }
      }
    }

    this.router.on(routes)
    this.router.resolve()
  }

  ale (page) {
    document.querySelectorAll('.page').forEach(p => p.classList.add('hide'))
    document.querySelector(`#${page}`).classList.remove('hide')
  }

  init () {
    this.routes()

    if (window.location.hash === '') {
      this.router.navigateByName('home')
    }

    (new DataMarkings())
  }
}
