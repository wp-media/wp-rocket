import React from 'react';
import PropTypes from 'prop-types';

import fetchWP from '../utils/fetchWP';

export default class ContactForm extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      error: false,
      submitted: false,
      name: '',
      email: '',
      message: '',
    };

    this.fetchWP = new fetchWP({
      restURL: this.props.wpObject.api_url,
      restNonce: this.props.wpObject.api_nonce,
    });
  }

  handleInputChange = (event) => {
    const target = event.target;
    const value = target.value;
    const name = target.name;

    this.setState({
      [name]: value
    });
  }

  handleSubmit = (event) => {
    event.preventDefault();

    this.fetchWP.post( 'submission', {
      name: this.state.name, 
      email: this.state.email,
      message: this.state.message, 
    } )
    .then(
      (json) => {
        this.setState({
          submitted: true,
          error: false
        }),
        console.log(`New Contact Submission: ${json.value}`)
      },
      (err) => this.setState({
        error: err.message
      }),
    );
  }

  render() {
    const contactForm = this.state.submitted ? <p>Thanks for getting in touch!</p> :
      <form onSubmit={this.handleSubmit}>
        <label>
        Name:
          <input
            type="text"
            name="name"
            onChange={this.handleInputChange}
          />
        </label>

        <label>
        Email:
          <input
            type="email"
            name="email"
            onChange={this.handleInputChange}
          />
        </label>

        <label>
        Message:
          <textarea 
            name="message"
            onChange={this.handleInputChange}
          />
        </label>

        <button
          className="button button-primary"
          onClick={this.handleSubmit}
        >Submit</button>

      </form>;

    const error = this.state.error ? <p className="error">{this.state.error}</p> : '';
      
    return (
      <div>
        {contactForm}
        {error}
      </div>
    );
  }
}

ContactForm.propTypes = {
  wpObject: PropTypes.object
};