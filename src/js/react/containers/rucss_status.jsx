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
			allow_optimization: false,
			error_message: '',
			code: 0,
			success: true,
		};

		this.enableOptimization = this.enableOptimization.bind(this);
	}

	getStatus() {
		wp.apiFetch(
			{
				url: this.props.wpRUCSSObject.api_url,
				method: 'POST'
			}
		).then(
			data => this.setState(
				{
					scan_status: typeof data.data.scan_status != 'undefined' ?  data.data.scan_status : this.state.scan_status,
					warmup_status: typeof data.data.warmup_status != 'undefined' ?  data.data.warmup_status : this.state.warmup_status,
					code: typeof data.code != 'undefined' ?  data.code : this.state.code,
					success: typeof data.success != 'undefined' ?  data.success : this.state.success,
					error_message: typeof data.message != 'undefined' ?  data.message : this.state.error_message,
					allow_optimization: typeof data.data.allow_optimization != 'undefined' ?  data.data.allow_optimization : this.state.allow_optimization,
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
		this.getStatus();
		this.computeProgress();

		this.timeout = setInterval(() => {
			this.getStatus();
			this.computeProgress();

			if ( this.state.progress > 100 ) {
				clearInterval(this.timeout);
			}
		}, 3000);
	}

	step1Completed() {
		return this.state.scan_status.completed;
	}

	step1Progress() {
		return this.state.scan_status.scanned;
	}

	step1MaxProgress() {
		return this.state.scan_status.total_pages;
	}

	step2Completed() {
		return this.state.warmup_status.completed;
	}


	step2Progress() {
		return this.state.warmup_status.warmed_count;
	}

	step2MaxProgress() {
		return this.state.warmup_status.total;
	}

	renderError() {
		let error;
		if ( ! this.state.success && this.state.error_message != '' ) {
			error = <div className="rucss-progress-error wpr-fieldWarning-title wpr-icon-important">
						{this.state.error_message}
					</div>;
		}
		return error;
	}

	renderScanStep() {
		let step1;
		if ( this.state.success ) {
			let scanTxt = this.props.wpRUCSSObject.wpr_rucss_translations.step1_txt;
			scanTxt = scanTxt.replace("{count}", this.state.scan_status.scanned);
			scanTxt = scanTxt.replace("{total}", this.state.scan_status.total_pages);

			let classNames = this.step1Completed() ? 'rucss-progress-step completed step1  wpr-icon-check' : 'rucss-progress-step step1';
			step1 = (<div className={classNames}>
						<div className="spinner"></div>
							{scanTxt}
					</div>);
		}
		return step1;
	}

	renderWarmupStep() {
		let step2;
		if ( this.state.success && this.step1Completed() ) {
			let scanTxt = this.props.wpRUCSSObject.wpr_rucss_translations.step2_txt;
			scanTxt = scanTxt.replace("{count}", this.state.warmup_status.warmed_count);
			scanTxt = scanTxt.replace("{total}", this.state.warmup_status.total);
			let classNames = this.step2Completed() ? 'rucss-progress-step completed step2  wpr-icon-check' : 'rucss-progress-step  step2';
			step2 = <div className={classNames}>
						<div className="spinner"></div>
						{scanTxt}
					</div>;
		}
		return step2;
	}

	renderRUCSSEnabled() {
		let rucssEnabled;
		if ( this.state.allow_optimization ) {
			rucssEnabled = <div className="rucss-progress-step completed wpr-icon-check">
								{this.props.wpRUCSSObject.wpr_rucss_translations.rucss_working}
							</div>;
		}
		return rucssEnabled;
	}

	renderButtonAllowOptimization() {
		let btn, duration;
		duration = this.state.warmup_status.duration;
		if ( ! this.state.allow_optimization && duration > 600 ) {
			btn = <div>
					<button className="wpr-button" onClick={this.enableOptimization}>
						{this.props.wpRUCSSObject.wpr_rucss_translations.rucss_btn}
					</button>
					<div className="wpr-field-description wpr-field-description-helper">
						{this.props.wpRUCSSObject.wpr_rucss_translations.rucss_btn_txt}
					</div>
				</div>;
		}
		return btn;
	}

	renderNotWarmedResourcesList() {
		let step2_list;
		if ( this.state.success && this.step1Completed() && this.state.warmup_status.notwarmed_resources.length > 0) {
			step2_list = <div className="rucss-progress-step wpr-icon-important rucss-progress-step2-list">
							{this.props.wpRUCSSObject.wpr_rucss_translations.warmed_list}
							<ul className="rucss-notwarmed-resources">
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

	enableOptimization( e ) {
		e.preventDefault();

		wp.apiFetch(
			{
				url: this.props.wpRUCSSObject.api_url,
				method: 'POST',
				data: { allow_optimization: true },
			}
		).then(
			data => this.setState(
				{
					scan_status: typeof data.data.scan_status != 'undefined' ?  data.data.scan_status : this.state.scan_status,
					warmup_status: typeof data.data.warmup_status != 'undefined' ?  data.data.warmup_status : this.state.warmup_status,
					code: typeof data.code != 'undefined' ?  data.code : this.state.code,
					success: typeof data.success != 'undefined' ?  data.success : this.state.success,
					error_message: typeof data.message != 'undefined' ?  data.message : this.state.error_message,
					allow_optimization: typeof data.data.allow_optimization != 'undefined' ?  data.data.allow_optimization : this.state.allow_optimization,
				}
			)
		);
	}

	renderRUCSSProgress() {
		let rucssProgress;
		if ( ! this.state.allow_optimization ) {
			rucssProgress = <div>
								<div className="wpr-field-description" dangerouslySetInnerHTML={{__html: this.props.wpRUCSSObject.wpr_rucss_translations.rucss_info_txt}}></div>
								<div className="rucss-progress-bar">
									<ProgressBar value={this.state.progress} max={this.state.max}/>
								</div>
							</div>;
		}
		return rucssProgress;
	}

	render() {
		return (
			<div className="wpr-field wpr-field--textarea">
				<div className="rucss-status wpr-field-description">
					{this.renderRUCSSProgress()}
					{this.renderError()}
					{this.renderScanStep()}
					{this.renderWarmupStep()}
					{this.renderNotWarmedResourcesList()}
					{this.renderButtonAllowOptimization()}
					{this.renderRUCSSEnabled()}
				</div>
			</div>
		);
	}
}

RUCSSStatus.propTypes = {
    wpRUCSSObject: PropTypes.object
};
