import React, {useState, useEffect, useCallback} from 'react';
import Container from '@material-ui/core/Container';
import Card from '@material-ui/core/Card';
import CardActions from '@material-ui/core/CardActions';
import CardContent from '@material-ui/core/CardContent';
import TextField from '@material-ui/core/TextField';
import Button from '@material-ui/core/Button';
import MuiAlert, {Color} from '@material-ui/lab/Alert';
import Typography from '@material-ui/core/Typography';
import {makeStyles} from '@material-ui/core/styles';
import {CssBaseline} from "@material-ui/core";
import AssignmentIndOutlinedIcon from '@material-ui/icons/AssignmentIndOutlined';
import Avatar from "@material-ui/core/Avatar";
import Grid from "@material-ui/core/Grid";
import Link from "../src/components/MaterialNextLink";

function createNotification(message: string, type: Color) {
    return <MuiAlert variant="standard" severity={type}>{message}</MuiAlert>;
}

const useStyles = makeStyles((theme) => ({
    paper: {
        marginTop: theme.spacing(8),
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
    },
    avatar: {
        margin: theme.spacing(1),
        backgroundColor: theme.palette.warning.main,
    },
    form: {
        width: '100%', // Fix IE 11 issue.
        marginTop: theme.spacing(1),
    },
    submit: {
        margin: theme.spacing(3, 0, 2),
    },
}));

export default function Registration() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [confirm, setConfirm] = useState('');
    const [confirmDirty, setConfirmDirty] = useState(false);
    const [passwordError, setPasswordError] = useState('');
    const [confirmError, setConfirmError] = useState('');
    const [emailError, setEmailError] = useState('');
    const [submitError, setSubmitError] = useState('');
    const [submit, setSubmit] = useState(false);
    const [successfulRegister, setSuccessfulRegister] = useState(false);

    const title: string = "Registierung";
    const emailPlaceHolder = "E-Mail";
    const passwordPlaceHolder: string = "Passwort";
    const confirmPlaceHolder: string = "Passwort bestätigen";
    const submitCaption: string = "Registrieren";
    const tooShortPassword: string = "Das Passwort muss mindestens 8 Zeichen lang sein.";
    const passwordsNotMatching: string = "Die Passwörter stimmen nicht überein.";
    const emptyEmail: string = "Bitte geben Sie eine E-Mail-Adresse ein.";
    const emptyPassword: string = "Bitte geben Sie ein Passwort ein.";
    const successMessage: string = 'Registrierung erfolgreich';

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

    const checkSubmitError = async (response: Response) => {
        const json = await response.json();
        if (response.status === 400) {
            setSubmitError(json.message);
        } else {
            setSuccessfulRegister(true);
        }
        return json;
    };

    const onSubmit = useCallback(async (e: React.FormEvent) => {
        e.preventDefault();
        setSubmit(true);
        setPasswordError(null);
        const hasBlankFields: boolean = email === '' || password === '' || confirmError === '';
        const hasErrors: boolean = !!emailError || !!passwordError || !!confirmError;

        if (hasBlankFields || hasErrors) {
            return;
        }
        const requestOptions = {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({email, password})
        };
        const response = await fetch(`${process.env.NEXT_PUBLIC_BACKEND_URL}/register`, requestOptions);
        return await checkSubmitError(response);
    }, [email, password, emailError, passwordError, confirmError]);

    const classes = useStyles();

    return <Container component="main" maxWidth="xs">
        <CssBaseline/>
        <div className={classes.paper}>
            <Avatar className={classes.avatar}>
                <AssignmentIndOutlinedIcon/>
            </Avatar>
            <Typography component="h1" variant="h5">{title}</Typography>
            <form className={classes.form} onSubmit={onSubmit} noValidate>
                {submitError && createNotification(submitError, 'error')}
                {successfulRegister && createNotification(successMessage, 'success')}
                <TextField variant="outlined"
                           margin="normal"
                           required autoFocus
                           fullWidth id="email" type="email" error={!!emailError} value={email}
                           onChange={e => setEmail(e.target.value)} label={emailPlaceHolder} helperText={emailError}
                           autoComplete="email" spellCheck="false"/>
                <TextField variant="outlined"
                           margin="normal"
                           required
                           fullWidth id="password" type="password" error={!!passwordError} value={password}
                           onChange={e => setPassword(e.target.value)} label={passwordPlaceHolder}
                           helperText={passwordError} autoComplete="new-password" spellCheck="false"/>
                <TextField variant="outlined"
                           margin="normal"
                           required
                           fullWidth id="password-confirm" type="password" error={!!confirmError} value={confirm}
                           onChange={e => handleConfirmChange(e.target.value)} label={confirmPlaceHolder}
                           helperText={confirmError} autoComplete="new-password" spellCheck="false"/>
                <Button
                    type="submit"
                    fullWidth
                    variant="contained"
                    color="primary"
                    className={classes.submit}
                >
                    {submitCaption}
                </Button>
                <Grid container>
                    <Grid item xs>
                        <Link href="/login" variant="body2">Bereits registriert oder du hast einen CIS-Account? Zum Login!</Link>
                    </Grid>
                </Grid>
            </form>
        </div>
    </Container>
}

