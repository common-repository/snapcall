function doFetch(host, data = {}) {
  return new Promise((resolve, reject) => {
    const dataBody = new FormData();
    Object.assign(data, {
      api_key: sc_user.api_key,
      api_secret: sc_user.api_secret
    });
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

async function handleForm(e) {
  const form = e.target || e.srcElement;
  if (!form.dataset.endpoint) return;
  const inputs = form.querySelectorAll('input');
  const loader = document.getElementById('loader');
  const action = form.dataset.endpoint.split('|');
  const cb = form.dataset.callback;
  const data = {};
  e.preventDefault();
  loader.style.display = 'inline-block';
  for (input of inputs) {
    if (input.type !== 'submit') {
      data[input.name] = input.value;
    }
  }
  const request = await doFetch(`${cfg[action[0]]}${action[1]}`, data);
  loader.style.display = 'none';
  if (cb) {
    const fn = window[cb];
    if (fn) fn(request, data);
  }
}

function register(res, data) {
  if (res.success) {
    const emailInput = document.getElementById('sc_login_email');
    const passwordInput = document.getElementById('sc_login_password');
    const loginForm = document.getElementById('snapcall_login_form');
    emailInput.value = data.email;
    passwordInput.value = data.password;
    loginForm.submit();
  } else {
    const errorDiv = document.getElementById('sc_register_error');
    let message;
    if (res.error === 'name') message = sc_str.registerErrName;
    if (res.error === 'email') message = sc_str.registerErrEmail;
    if (res.error === 'password') message = sc_str.registerErrPassword;
    if (res.error === 'password_confirm') message = sc_str.registerErrPasswordConfirm;
    if (res.error === 'stripe') message = sc_str.registerErrStripe;
    if (res.error === 'user') message = sc_str.registerErrUser;
    if (res.error === 'company') message = sc_str.registerErrCompany;
    if (res.error === 'upgrade') message = sc_str.registerErrUpgrade;
    if (res.error === 'subscribe') message = sc_str.registerErrSubscribe;
    if (res.error === 'update') message = sc_str.registerErrUpdate;
    if (!message) message = sc_str.errStandard;
    errorDiv.innerHTML = message;
  }
}

function firstButton(res) {
  if (res.success) {
    const successDiv = document.getElementById('sc_first_button_success');
    const firstButtonDiv = document.getElementsByClassName('sc-first-button')[0];
    successDiv.innerHTML = `${sc_str.buttonSuccess}<br>${sc_str.manage} <a href="https://admin.snapcall.io/login.php" target="_blank">${sc_str.backOffice}</a>`;
    firstButtonDiv.style.display = 'none';
  } else {
    const errorDiv = document.getElementById('sc_first_button_error');
    let message;
    if (res.error === 'licence') {
      message = sc_str.firstButtonErrLicence;
    }
    if (res.error === 'agentid') {
      message = sc_str.firstButtonErrAgentId;
    }
    if (res.error === 'not_first') {
      message = `${sc_str.firstButtonErrNotFirst} (^○^)`;
    }
    if (!message) {
      message = sc_str.errStandard;
    }
    errorDiv.innerHTML = message;
  }
}

function tabClick(e) {
  const el = e.target || e.srcElement;
  const href = el.getAttribute('href').substr(1);
  const elContent = document.getElementById(`${href}-tab`);
  if (!elContent) return;
  const activeTab = document.getElementsByClassName('nav-tab-active');
  e.preventDefault();
  for (tab of activeTab) {
    const tabHref = tab.getAttribute('href').substr(1);
    const tabContent = document.getElementById(`${tabHref}-tab`);
    tab.classList.remove('nav-tab-active');
    tabContent.classList.remove('tab-content-active');
  }
  el.classList.add('nav-tab-active');
  elContent.classList.add('tab-content-active');
}

(async function() {
  const nav = document.getElementsByClassName('sc-nav-tab');
  const forms = document.getElementsByTagName('form');
  const status = document.getElementById('statusText');
  const loader = document.getElementById('loader');

  if (!window.fetch) {
    status.innerHTML = sc_str.errBrowser;
    return;
  }
  for (tab of nav) {
    tab.addEventListener('click', tabClick);
  }
  for (form of forms) {
    if (form.classList.contains('snapcall-form')) {
      form.addEventListener('submit', handleForm);
    }
  }
  if (sc_user && sc_user.id && sc_user.api_key && sc_user.api_secret) {
    status.innerHTML = sc_str.checking;
    loader.style.display = 'inline-block';
    const [dbCms, buttons] = await Promise.all([
      doFetch(`${cfg.api}/user/cms`),
      doFetch(`${cfg.api}/widget/list`)
    ]);
    if (dbCms.wordpress !== sc_user.link) {
      const request = await doFetch(`${cfg.api}/user/set_cms`, {
        cms: 'wordpress',
        cms_link: sc_user.link
      });
      if (!request.success) {
        status.innerHTML = sc_str.errStandardMail;
        loader.style.display = 'none';
        return;
      }
    }
    status.innerHTML = sc_str.cmsConnected;
    loader.style.display = 'none';
    if (buttons.length < 1) {
      const firstButtonDiv = document.getElementsByClassName('sc-first-button')[0];
      firstButtonDiv.style.display = 'block';
    } else {
      status.innerHTML += `<br>${sc_str.manage} <a href="https://admin.snapcall.io/login.php" target="_blank">${sc_str.backOffice}</a>`;
    }
  }
})();