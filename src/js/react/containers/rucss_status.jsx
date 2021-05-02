import React, { Component } from 'react';
import PropTypes from 'prop-types';

import ProgressBar from '../components/progress_bar';

export default class RUCSSStatus extends Component {
	constructor(props) {
		super(props);

		// Set the default states
		this.state = {
			progress:      0,
			data:         {},
			errorMessage: '',
		};
	}

	getStatus() {
		wp.apiFetch( { url: this.props.wpObject.api_url } ).then(
			data => this.setState({ data })
		);
	}

	computeProgress() {
		if (this.state.data.code == 'rest_forbidden' || this.state.data.success == false) {
			clearInterval(this.timeout);
			this.setState(
				{
					errorMessage: this.state.data.message,
				}
			);
			return;
		}

		let progress = this.state.progress + 10;
		this.setState(
			{
				progress: progress,
			}
		);
	}

    componentDidMount() {
		this.timeout = setInterval(() => {
			this.getStatus();
			this.computeProgress();

			if ( this.state.progress > 100 ) {
				clearInterval(this.timeout);
			}
		}, 1000);
	}

	render() {
		return (
			<div className="rucss-status">
				<div className="rucss-progress-bar">
					<ProgressBar value={this.state.progress} max={100}/>
				</div>
				<div className="rucss-progress-error">
					{this.state.errorMessage}
				</div>
				<div className="rucss-progress-step1">

				</div>
			</div>
		);
	}
}

RUCSSStatus.propTypes = {
    wpObject: PropTypes.object
};
