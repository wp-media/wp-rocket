import React from 'react';
import PropTypes from 'prop-types';

export default class Notice extends React.Component {
  componentDidMount() { // As soon as the component has mounted
    if (this.props.duration > 0) { // if the duration prop is greater than zero
      this.dismissTimeout = window.setTimeout(this.props.onDismissClick, this.props.duration); // then, after the set duration has passed, run the onDismissClick function that has been passed down as a prop from our Admin.jsx container
    } // (otherwise, do nothing until manually dismissed)
  }

  componentWillUnmount() { // When the component is about to removed from the DOM
    if (this.dismissTimeout) { // If this.dismissTimeout was set in componentDidMount()
      window.clearTimeout(this.dismissTimeout); // reset the timer when the notice is dismissed (and therefore 'unmounted')
    }
  }

  render() {
    let dismiss; // define dismiss as an empty variable

    if (this.props.showDismiss) { // if our showDismiss prop is set to true
      dismiss = ( // then set the dismiss variable to contain our dismiss button markup
        <span tabIndex="0" className="notice_dismiss" onClick={ this.props.onDismissClick /* when this element is clicked, fire the onDismissClick function that we've passed down as a prop from Admin.jsx */ } >
          <span className="dashicons dashicons-dismiss"></span>
          <span className="screen-reader-text">Dismiss</span>
        </span>
      );
    }

    return ( // The returned markup uses the standard WordPress notice classes, so no extra styling required. The resulting notice class and message will depend on the contents of the 'notice' prop object, passed down from Admin.jsx.
      <div className={`notice is-dismissible notice-${this.props.notice.type}`}>
        <p><strong>{this.props.notice.message}</strong></p>
        { dismiss }
      </div>
    );
  }
}

Notice.defaultProps = { // Here we set default values for some of our props
  duration: 4000,
  showDismiss: true,
  onDismissClick: null,
};

Notice.propTypes = { // And define our propTypes
  duration: PropTypes.number,
  showDismiss: PropTypes.bool,
  onDismissClick: PropTypes.func,
  notice: PropTypes.oneOfType([
    PropTypes.bool,
    PropTypes.shape({
      type: PropTypes.string,
      message: PropTypes.string
    })
  ]),
};
