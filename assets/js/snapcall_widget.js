function doFetch(host, data) {
  return new Promise((resolve, reject) => {
    const dataBody = new FormData();
    for (key of Object.keys(data)) {
      dataBody.append(key, data[key]);
    }
    fetch(host, {
      method: 'POST',
      body: dataBody
    })
      .then(res => res.json())
      .then(json => resolve(json))
      .catch(err => reject(err));
  });
}

(async function() {
  if (sc_obj && sc_obj.uid) {
    const widget = await doFetch(`${sc_obj.api}/user/get_widget`, {
      uid: sc_obj.uid,
      display_page: sc_obj.category || '',
      cart_value: sc_obj.cart_value
    });
    if (widget && widget.bid) {
      const sc_script = document.createElement('script');
      sc_script.setAttribute('src', 'https://snap.snapcall.io/snapapp.min.js');
      sc_script.setAttribute('btn-bid', widget.bid);
      document.getElementsByTagName('head')[0].appendChild(sc_script);
    }
  }
})();
