import { useState, useEffect, useCallback } from 'react';
import Container from '@material-ui/core/Container';
import Card from '@material-ui/core/Card';
import CardActions from '@material-ui/core/CardActions';
import CardContent from '@material-ui/core/CardContent';
import TextField from '@material-ui/core/TextField';
import Button from '@material-ui/core/Button';
import Typography from '@material-ui/core/Typography';
import { makeStyles } from '@material-ui/core/styles';

const useStyles = makeStyles(() => ({
    root: {
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center'
    },
    content: {
        flex: '1',
        margin: '1rem 2rem 0'
    },
    form: {
        display: 'flex',
        flexDirection: 'column'
    },
    actions: {
        margin: '0 2rem 1rem'
    },
    submit: {
        marginLeft: 'auto'
    }
}))

export default function Registration() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [confirm, setConfirm] = useState('');
    const [confirmDirty, setConfirmDirty] = useState(false);
    const [passwordError, setPasswordError] = useState('');
    const [confirmError, setConfirmError] = useState('');
    const [emailError, setEmailError] = useState('');
    const [submit, setSubmit] = useState(false);

    const title: string = "Registierung";
    const emailPlaceHolder = "E-Mail";
    const passwordPlaceHolder: string = "Passwort";
    const confirmPlaceHolder: string = "Passwort bestätigen";
    const submitCaption: string = "Registrieren";
    const tooShortPassword: string = "Das Passwort muss mindestens 8 Zeichen lang sein.";
    const passwordsNotMatching: string = "Die Passwörter stimmen nicht überein.";
    const emptyEmail: string = "Bitte geben Sie eine E-Mail-Adresse ein.";
    const emptyPassword: string = "Bitte geben Sie ein Passwort ein.";

    const minPasswordLength: number = 8;

    useEffect(() => {
        if (emailError && email.length > 0) {
            setEmailError(null);
        }
    }, [email]);

    useEffect(() => {
        const invalidPasswordLength: boolean = password.length < minPasswordLength && password.length > 0;
        invalidPasswordLength ? setPasswordError(tooShortPassword) : setPasswordError(null);
    }, [password]);

    useEffect(() => {
        password !== confirm && confirmDirty ? setConfirmError(passwordsNotMatching) : setConfirmError(null);
    }, [password, confirm]);

    useEffect(() => {
        const isEmptyPassword: boolean = password.length === 0;
        const isEmptyEmail: boolean = email.length === 0;
        if (isEmptyPassword && submit) {
            setPasswordError(emptyPassword);
        }
        if (isEmptyEmail && submit) {
            setEmailError(emptyEmail);
        }
        setSubmit(false);
    }, [submit]);

    const handleConfirmChange = (value: string) => {
        setConfirm(value);
        setConfirmDirty(true);
    };

    const onSubmit = useCallback(async () => {
        setSubmit(true);
        setPasswordError(null);
        const hasBlankFields: boolean = email === '' || password === '' || confirmError === '';
        const hasErrors: boolean = !!emailError || !!passwordError || !!confirmError;

        if (hasBlankFields || hasErrors) {
            return;
        }
        const requestOptions = {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ email, password })
        };
        const response = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL}/register`, requestOptions);
        return await response.json();
    }, [email, password, emailError, passwordError, confirmError]);

    const classes = useStyles();

    return <Container maxWidth="sm">
        <Card variant="outlined">
            <CardContent className={classes.content}>
                <Typography variant="h4">{title}</Typography>
                <form className={classes.form} noValidate>
                    <TextField required id="email" type="email" error={!!emailError} value={email} onChange={e => setEmail(e.target.value)} label={emailPlaceHolder} helperText={emailError} autoComplete="email" spellCheck="false" />
                    <TextField required id="password" type="password" error={!!passwordError} value={password} onChange={e => setPassword(e.target.value)} label={passwordPlaceHolder} helperText={passwordError} autoComplete="new-password" spellCheck="false" />
                    <TextField required id="password-confirm" type="password" error={!!confirmError} value={confirm} onChange={e => handleConfirmChange(e.target.value)} label={confirmPlaceHolder} helperText={confirmError} autoComplete="new-password" spellCheck="false" />
                </form>
            </CardContent>
            <CardActions className={classes.actions}>
                <Button className={classes.submit} variant="contained" color="primary" type="submit" onClick={onSubmit}>{submitCaption}</Button>
            </CardActions>
        </Card>
    </Container>
}

