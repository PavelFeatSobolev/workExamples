import React from "react";
import TimerCountDown from "../timers/timerCountDown.js";

class UserVerification extends React.Component
{
    constructor(props)
    {
        super(props);
        this.state = {codeSend: false, resendButton: false};
        this.sendCodeConfirm = this.sendCodeConfirm.bind(this);
        this.setResendButtonFlag = this.setResendButtonFlag.bind(this);
    }

    sendCodeConfirm(e)
    {
        e.preventDefault();
        this.setState({codeSend: true});
    }

    resendCodeConfirm(e)
    {
        e.preventDefault();
        this.setResendButtonFlag();
    }

    setResendButtonFlag(e)
    {
        e.preventDefault();
        this.setState({resendButton:  (this.state.resendButton) ?  false : true});
    }

    render() {

        if (!this.state.codeSend) {
            return (<a href='' className="sendMessage" onClick={this.sendCodeConfirm}>Получить код</a>);
        } else {
            if (!this.state.resendButton) {
                return (<div>
                    <TimerCountDown afterCallback={this.setResendButtonFlag} startTime={10} className="countDown"/>
                </div>);
            }

            return(<a className="resendMessage" onClick={this.resendCodeConfirm}>Отправить код еще раз</a>);
        }
    };
}

export default UserVerification;