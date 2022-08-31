import React from 'react';

class PopUp extends React.Component
{
    render() {
        return (
            <div className={this.props.className}>
                <h3>{this.props.textTitle}</h3>
                <div className='popupContent'>

                </div>
            </div>
        )
    }
}