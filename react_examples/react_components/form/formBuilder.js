import React from "react";

import InputBuilder from "./inputBuilder.js";
import LabelBuilder from "./labelBuilder";

class FormBuilder extends React.Component
{
    prepareFormParams()
    {
        let props = this.props.formSetting;
        let title =  (props.title) ? <h3 className='formTitle'>{props.title}</h3> : '';

        return <form action={props.formParams.action} method={props.formParams.method} name={props.formParams.name}>
            {title}
            {this.formTemplateConstructor()}
        </form>;
    }

    formTemplateConstructor()
    {
        let fields = this.props.formSetting.formFields;
        let templateView = [];

        if (fields) {
            for (let key in fields) {
                if (fields[key].type) {
                    let classMethod = fields[key].type + "PrepareView";
                    templateView.push(this[classMethod](fields[key]));
                }
            }
        }

        return templateView;
    }

    inputPrepareView (inputObj)
    {
        return  <InputBuilder name={inputObj.name} type={inputObj.type}/>
    }

    labelPrepareView(labelObj)
    {
       return  <LabelBuilder forHtml={labelObj.htmlFor} text={labelObj.text}/>
    }


    render()
    {
        const templateForm = this.prepareFormParams();
        return(<div className={this.props.formSetting.classFormContainer}>{templateForm}</div>);
    }
}

export default FormBuilder;