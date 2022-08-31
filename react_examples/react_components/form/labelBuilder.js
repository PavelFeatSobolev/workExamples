import React from "react";

class LabelBuilder extends React.Component
{
    render() {
        return (
            <label>{this.props.text}</label>
        )
    };
}
export default LabelBuilder;
