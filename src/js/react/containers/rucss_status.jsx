import React, { Component } from 'react';
import PropTypes from 'prop-types';

import ProgressBar from '../components/progress_bar';

export default class RUCSSStatus extends Component {
	constructor(props) {
		super(props);

		// Set the default states
		this.state = {
			progress: 0,
		};
// console.log(this.props.wpObject);
		// this.fetchWP = new fetchWP({
		//   restURL: this.props.wpObject.api_url,
		//   restNonce: this.props.wpObject.api_nonce,
		// });

		// wp.apiFetch( { path: this.props.wpObject.api_url } ).then( function( posts ){
		//  console.log( 'Title of the first item is: ' + posts[0].title.rendered );
		// } );

	}

    componentDidMount() {
		let progress = 0;
		this.timeout = setInterval(() => {
			this.setState(
				{
					progress: progress.toFixed(0),
				},
				() => {
					progress = progress + 1;
					if (progress > 100) {
						clearInterval(this.timeout);
					}
				},
			);
		}, 1000);
	}

	render() {
		return (
			<div className="rucss-status">
				<div className="rucss-progress-bar">
					<ProgressBar value={this.state.progress} max={100}/>
				</div>
			</div>
		);
	}
}

RUCSSStatus.propTypes = {
    wpObject: PropTypes.object
};
