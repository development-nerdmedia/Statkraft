export let dataLayerHandler = {
  sendData: function (category, action, label) {
    const objGTMEvent = {
      "event": "virtualEvent",
      "category": category,
      "action": action,
      "label": label
    }
    if (window.dataLayer || window.dataLayerCCF){
      if(window.dataLayerCCF)
      {
        window.dataLayerCCF.push(objGTMEvent);
        console.log("DataLayer::Push => Usando CCF", objGTMEvent);
      }else{
        if(window.dataLayer)
        {
          window.dataLayer.push(objGTMEvent);
          console.log("DataLayer::Push", objGTMEvent);
        }else{
          console.log('Aun no se implementó el Analitics')
        }
      }      
    } else {
      console.log('Aun no se implementó el Analitics')
    }
  },

  sendDataAsync: function (category, action, label) {
    const objGTMEvent = {
      "event": "virtualEvent",
      "category": category,
      "action": action,
      "label": label
    }
    if (window.dataLayer || window.dataLayerCCF){
      if(window.dataLayerCCF)
      {
        window.dataLayerCCF.push(objGTMEvent);
        console.log("DataLayer::Push => Usando CCF", objGTMEvent);
      }else{
        if (window.dataLayer){
          window.dataLayer.push(objGTMEvent);
          console.log("DataLayer::Push", objGTMEvent);
        }else{
          console.log('Aun no se implementó el Analitics')
        }
      }      
    } else {
        console.log('Aun no se implementó el Analitics')
    }
  }
}
