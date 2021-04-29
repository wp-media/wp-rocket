import React, { Component } from 'react';
import PropTypes from 'prop-types';

import fetchWP from '../utils/fetchWP';

import Notice from '../components/notice';

export default class Admin extends Component {
  constructor(props) {
    super(props);

    // Set the default states
    this.state = {
      email: '',
      savedEmail: '',
      notice: false,
    };

    this.fetchWP = new fetchWP({
      restURL: this.props.wpObject.api_url,
      restNonce: this.props.wpObject.api_nonce,
    });

    // Get the currently set email address from our /admin endpoint and update the email state accordingly
    this.getSetting();
  }

  getSetting = () => {
    this.fetchWP.get( 'admin' )
    .then(
      (json) => this.setState({
        email: json.value,
        savedEmail: json.value
      }),
      (err) => console.log( 'error', err )
    );
  };

  updateSetting = () => {
    this.fetchWP.post( 'admin', { email: this.state.email } )
    .then(
      (json) => this.processOkResponse(json, 'saved'),
      (err) => this.setState({
        notice: {
          type: 'error',
          message: err.message, // The error message returned by the REST API
        }
      })
    );
  }

  deleteSetting = () => {
    this.fetchWP.delete( 'admin' )
    .then(
      (json) => this.processOkResponse(json, 'deleted'),
      (err) => console.log('error', err)
    );
  }

  processOkResponse = (json, action) => {
    if (json.success) {
      this.setState({
        email: json.value,
        savedEmail: json.value,
        notice: {
          type: 'success',
          message: `Setting ${action} successfully.`,
        }
      });
    } else {
      this.setState({
        notice: {
          type: 'error',
          message: `Setting was not ${action}.`,
        }
      });
    }
  }

  updateInput = (event) => {
    this.setState({
      email: event.target.value,
    });
  }

  handleSave = (event) => {
    event.preventDefault();
    if ( this.state.email === this.state.savedEmail ) {
      this.setState({
        notice: {
          type: 'warning',
          message: 'Setting unchanged.',
        }
      });
    } else {
      this.updateSetting();
    }
  }

  handleDelete = (event) => {
    event.preventDefault();
    this.deleteSetting();
  }

  clearNotice = () => {
    this.setState({
      notice: false,
    });
  }

  render() {
    let notice;

    if ( this.state.notice ) {
      notice = <Notice notice={this.state.notice} onDismissClick={this.clearNotice} />
    }

    return (
      <div className="wrap">
        {notice}
        <form>
          <h1>Contact Form Settings</h1>

          <label>
          Notification Email:
            <input
              type="email"
              value={this.state.email}
              onChange={this.updateInput}
            />
          </label>

          <button
            id="save"
            className="button button-primary"
            onClick={this.handleSave}
          >Save</button>

          <button
            id="delete"
            className="button button-primary"
            onClick={this.handleDelete}
          >Delete</button>
        </form>
      </div>
    );
  }
}

Admin.propTypes = {
  wpObject: PropTypes.object
};
