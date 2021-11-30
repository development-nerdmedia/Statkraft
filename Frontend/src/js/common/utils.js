window.scrollFix = (toElement) => {
  let offset = document.querySelector(toElement).offsetTop

  window.scrollTo({
    top: offset,
    behavior: 'smooth',
  })
}

