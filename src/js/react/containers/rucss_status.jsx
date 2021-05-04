import React, { Component } from 'react';
import PropTypes from 'prop-types';

import ProgressBar from '../components/progress_bar';

export default class RUCSSStatus extends Component {
	constructor(props) {
		super(props);

		// Set the default states
		this.state = {
			progress:      0,
			scan_status: {
				scanned: 0,
				fetched: 0,
				total_pages: 0,
			},
			warmup_status: {
				total: 0,
				warmed_count: 0,
				notwarmed_resources: []
			},
			error_message: '',
			code: 0,
			success: true,
		};
	}

	getStatus() {
		wp.apiFetch(
			{
				url: this.props.wpObject.api_url,
				method: 'POST'
			}
		).then(
			data => this.setState(
				{
					scan_status: data.data.scan_status,
					warmup_status: data.data.warmup_status,
					code: data.code,
					success: data.success,
					error_message: data.message
				}
			)
		);
	}

	computeProgress() {
		if (this.state.code == 'rest_forbidden' || this.state.success == false) {
			clearInterval(this.timeout);
			this.setState(
				{
					error_message: this.state.error_message,
				}
			);
			return;
		}

		// let scan_status   = this.state.scan_status;
		// let warmup_status = this.state.warmup_status;

		let progress = this.state.progress + 0.1;
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
		let error, step1, step2, step2_list;
		if ( '' != this.state.error_message) {
			error = <div className="rucss-progress-error">
						{this.state.error_message}
					</div>;
		}

		if ( typeof this.state.scan_status != 'undefined' ) {
			step1 = <div className="rucss-progress-step1">
						Scanning {this.state.scan_status.scanned} from {this.state.scan_status.total_pages}
					</div>;
		}

		if ( typeof this.state.warmup_status != 'undefined' ) {
			step2 = <div className="rucss-progress-step2">
						Warming resources {this.state.warmup_status.warmed_count} from {this.state.warmup_status.total}
					</div>;
		}

		if ( typeof this.state.warmup_status != 'undefined' ) {
			step2_list = <div className="wpr-fieldsContainer-helper wpr-icon-important rucss-progress-step2-list">
							Not warmed resources list:
							<ul className="list-group">
								{this.state.warmup_status.notwarmed_resources.map(resource => (
									<li key="{resource}" className="list-group-item list-group-item-primary">
										{resource}
									</li>
								))}
							</ul>
						</div>;
		}

		return (
			<div className="wpr-field wpr-field--textarea wpr-field--children">
				<div className="rucss-status wpr-field-description">
					<div className="rucss-progress-bar">
						<ProgressBar value={this.state.progress} max={100}/>
					</div>
					{error}
					{step1}
					{step2}
					{step2_list}
				</div>
			</div>
		);
	}
}

RUCSSStatus.propTypes = {
    wpObject: PropTypes.object
};
