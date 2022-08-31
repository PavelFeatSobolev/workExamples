import React from 'react';

class TimerCountDown extends React.Component
{
    constructor(props)
    {
        super(props);
        this.state = {currentTime: props.startTime};
    }

    componentDidMount()
    {
        this.timerID = setInterval(() => this.updateStateTime(), 1000);
    }

    componentWillUnmount() {
        clearInterval(this.timerID);
    }

    updateStateTime()
    {
        if (this.state.currentTime > 0) {
            let newTime = this.state.currentTime - 1;
            this.setState({currentTime: newTime});
        } else {
           this.props.afterCallback();
        }
    }

    render() {
        return (
            <span className={this.props.className}>{this.state.currentTime}</span>
        )
    };
}

export default TimerCountDown;
