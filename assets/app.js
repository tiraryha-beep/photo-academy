(() => {
  'use strict';
  const root = document.querySelector('#photo-academy');
  if (!root) return;
  const config = window.PHOTO_ACADEMY_CONFIG || {};
  const form = root.querySelector('#lead-form');
  const status = root.querySelector('#form-status');
  const submit = form.querySelector('button[type=submit]');
  const ready = Boolean(config.endpoint && config.privacyUrl);
  if (ready) {
    root.querySelector('#integration-note').textContent = 'Vos coordonnées servent uniquement au traitement de votre demande.';
    const link = root.querySelector('#privacy-link');
    link.href = config.privacyUrl;
  }
  root.querySelectorAll('a[href="#confidentialite"]').forEach(link => link.addEventListener('click', event => {
    event.preventDefault();
    if (config.privacyUrl) window.location.assign(config.privacyUrl);
    else root.querySelector('#privacy-dialog').showModal();
  }));
  root.querySelector('.close-dialog').addEventListener('click', () => root.querySelector('dialog').close());
  form.addEventListener('submit', async event => {
    event.preventDefault();
    if (!form.reportValidity()) return;
    if (!ready) {
      status.textContent = 'Les inscriptions en ligne ne sont pas encore ouvertes : le service de réception doit être configuré. Aucune donnée n’a été envoyée.';
      return;
    }
    submit.disabled = true;
    status.textContent = 'Transmission de votre demande…';
    const payload = Object.fromEntries(new FormData(form));
    payload.priceConsent = form.elements.priceConsent.checked;
    payload.contactConsent = form.elements.contactConsent.checked;
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 15000);
    try {
      const endpoint = new URL(config.endpoint, location.href);
      if (endpoint.protocol !== 'https:' && endpoint.hostname !== 'localhost') throw new Error('HTTPS requis');
      const response = await fetch(endpoint, {
        method: 'POST', headers: { 'Content-Type': 'application/json', ...(config.nonce ? {'X-WP-Nonce': config.nonce} : {}) },
        body: JSON.stringify(payload), signal: controller.signal
      });
      const result = await response.json();
      if (!response.ok || result.success !== true || !result.id) throw new Error('Réception non confirmée');
      status.textContent = 'Merci ! Votre demande a bien été reçue par Photo Academy. L’équipe pourra vous contacter pour la suite de votre inscription.';
      form.reset();
      // Le serveur doit confirmer un enregistrement durable avant toute conversion.
      if (config.trackingConsent === true) {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({event: 'generate_lead', form_id: 'photo_academy', lead_id: String(result.id)});
        if (typeof window.fbq === 'function') window.fbq('track', 'Lead', {}, {eventID: String(result.id)});
      }
      root.dispatchEvent(new CustomEvent('photoacademy:lead-received', {detail: {id: result.id}}));
    } catch (_) {
      status.textContent = 'La réception n’a pas pu être confirmée. Vos champs sont conservés. Réessayez dans quelques instants ; aucune confirmation d’inscription n’a été émise.';
    } finally { clearTimeout(timeout); submit.disabled = false; }
  });
})();
