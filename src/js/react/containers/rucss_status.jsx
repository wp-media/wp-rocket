import React, { Component } from 'react';
import PropTypes from 'prop-types';

import ProgressBar from '../components/progress_bar';

export default class RUCSSStatus extends Component {
	constructor(props) {
		super(props);

		// Set the default states
		this.state = {
			progress:      0,
			max:           0,
			scan_status: {
				scanned: 0,
				fetched: 0,
				total_pages: 0,
				completed: false,
				duration: 0,
			},
			warmup_status: {
				total: 0,
				warmed_count: 0,
				notwarmed_resources: [],
				completed: false,
				duration: 0,
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

		let progress = 0;
		let max = 0;
		if ( ! this.step1Completed() ){
			progress = this.step1Progress();
			max      = this.step1MaxProgress();
		}

		if ( this.step1Completed() && ! this.step2Completed() ){
			progress = this.step2Progress();
			max      = this.step2MaxProgress();
		}

		if ( this.step1Completed() && this.step2Completed() ){
			clearInterval(this.timeout);
			progress = 100;
			max      = 100;
		}

		this.setState(
			{
				progress: progress,
				max: max,
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

	step1Completed() {
		let step1Completed = false;
		if ( this.state.scan_status != 'undefined' ) {
			step1Completed = this.state.scan_status.completed;
		}

		return step1Completed;
	}

	step1Progress() {
		let step1Progress = 0;
		if ( this.state.scan_status != 'undefined' ) {
			step1Progress = this.state.scan_status.scanned;
		}

		return step1Progress;
	}

	step1MaxProgress() {
		let step1MaxProgress = 0;
		if ( this.state.scan_status != 'undefined' ) {
			step1MaxProgress = this.state.scan_status.total_pages;
		}

		return step1MaxProgress;
	}

	step2Completed() {
		let step2Completed = false;
		if ( this.state.warmup_status != 'undefined' ) {
			step2Completed = this.state.warmup_status.completed;
		}

		return step2Completed;
	}


	step2Progress() {
		let step2Progress = 0;
		if ( this.state.warmup_status != 'undefined' ) {
			step2Progress = this.state.warmup_status.warmed_count;
		}

		return step2Progress;
	}

	step2MaxProgress() {
		let step2MaxProgress = 0;
		if ( this.state.warmup_status != 'undefined' ) {
			step2MaxProgress = this.state.warmup_status.total;
		}

		return step2MaxProgress;
	}

	renderError() {
		let error;
		if ( '' != this.state.error_message) {
			error = <div className="rucss-progress-error">
						{this.state.error_message}
					</div>;
		}
		return error;
	}

	renderScanStep() {
		let step1;
		if ( typeof this.state.scan_status != 'undefined' ) {
			let classNames = this.step1Completed() ? 'rucss-progress-step1  wpr-icon-important' : 'rucss-progress-step1  wpr-icon-refresh';
			step1 = (<div className={classNames}>
						Scanning {this.state.scan_status.scanned} from {this.state.scan_status.total_pages} in {this.state.scan_status.duration} seconds
					</div>);
		}
		return step1;
	}

	renderWarmupStep() {
		let step2;
		if ( this.step1Completed() && typeof this.state.warmup_status != 'undefined' ) {
			let classNames = this.step2Completed() ? 'rucss-progress-step2  wpr-icon-important' : 'rucss-progress-step2  wpr-icon-refresh';
			step2 = <div className={classNames}>
						Warming resources {this.state.warmup_status.warmed_count} from {this.state.warmup_status.total} in {this.state.warmup_status.duration} seconds
					</div>;
		}
		return step2;
	}

	renderNotWarmedResourcesList() {
		let step2_list;
		if ( this.step1Completed() && this.state.warmup_status.notwarmed_resources.length > 0) {
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

		return step2_list;
	}

	render() {
		return (
			<div className="wpr-field wpr-field--textarea wpr-field--children">
				<div className="rucss-status wpr-field-description">
					<div className="rucss-progress-bar">
						<ProgressBar value={this.state.progress} max={this.state.max}/>
					</div>
					{this.renderError()}
					{this.renderScanStep()}
					{this.renderWarmupStep()}
					{this.renderNotWarmedResourcesList()}
				</div>
			</div>
		);
	}
}

RUCSSStatus.propTypes = {
    wpObject: PropTypes.object
};
