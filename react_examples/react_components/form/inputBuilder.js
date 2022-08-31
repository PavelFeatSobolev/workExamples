import React from 'react';

class InputBuilder extends React.Component
{
    render() {
        return (
            <input type={this.props.type}
                   name={this.props.name}
                   placeholder={this.props.placeholder}
                   className={this.props.className}
                   id={this.props.id}
            />
        );
    }
}
export default InputBuilder;