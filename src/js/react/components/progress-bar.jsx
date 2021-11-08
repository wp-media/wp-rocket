import React from 'react';

export default class ProgressBar extends React.Component {
	constructor(props) {
		super(props);
		this.state = {
			value: 0,
			max: 100,
		};
	}

	render() {
		return (
			<progress value={this.props.value} max={this.props.max} />
	    );
	}
}
