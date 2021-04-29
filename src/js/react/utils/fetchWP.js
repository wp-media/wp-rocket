import fetch from 'isomorphic-fetch';

const methods = [
  'get',
  'post',
  'put',
  'delete',
];

export default class fetchWP {
  constructor( options = {} ) {
    this.options = options;

    if ( !options.restURL )
    throw new Error('restURL option is required');

    if ( !options.restNonce )
    throw new Error('restNonce option is required');

    methods.forEach(method => {
      this[method] = this._setup(method);
    });
  }

  _setup( method ) {
    return (endpoint = '/', data = false) => {
      let fetchObject = {
        credentials: 'same-origin',
        method: method,
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-WP-Nonce': this.options.restNonce,
        }
      };

      if ( data ) {
        fetchObject.body = JSON.stringify(data);
      }

      return fetch(this.options.restURL + endpoint, fetchObject)
      .then(response => {
        return response.json().then(json => {
          return response.ok ? json : Promise.reject(json);
        });
      });
    }
  }
}
