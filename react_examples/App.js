import logo from './logo.svg';

import UserVerification from "./react_components/blocks/userVerification.js";
import FormBuilder from "./react_components/form/formBuilder.js";

//import './App.css';
import './css/default.css';

const formSetting = {
    'title' : 'Вход',
    'classFormContainer': 'enterForm',
    'formParams': {
        method: 'post',
        action: '/enter.js',
        name: 'userEnter'
    },
    'formFields': {
        0: {
            'type': 'label',
            'text': 'Телефон'
        },
        1: {
            'type': 'input',
            'name': 'PHONE'
        }
    }
};

function App() {
    return (
        <div className="wrapper">
            <header className="header">
                <div className='logo'>Science data</div>
                <nav className="topMenu">
                    <ul>
                        <li className='active'>Главная</li>
                        <li><a href='/'>Сейсмическая активность</a></li>
                        <li><a href='/'>Графики и аналитика</a></li>
                        <li><a href='/'>Контакты</a></li>
                    </ul>
                    <ul className='personal'>
                        <li><a className='enter' href='/enter.js'>Войти</a></li>
                        <li><a className='registry' href='/registration.js'>Регистрация</a></li>
                    </ul>
                </nav>
            </header>
            <div className='content'>

                <FormBuilder formSetting={formSetting}/>
                <div>
                    <UserVerification/>
                </div>
            </div>
           <footer className='footer'>
               <div className='socialNetwork'></div>
               <div className='copyright'></div>
           </footer>
        </div>
    );
}

export default App;
