import React, {useState} from 'react';
import Avatar from '@material-ui/core/Avatar';
import Button from '@material-ui/core/Button';
import CssBaseline from '@material-ui/core/CssBaseline';
import TextField from '@material-ui/core/TextField';
import Grid from '@material-ui/core/Grid';
import LockOutlinedIcon from '@material-ui/icons/LockOutlined';
import Typography from '@material-ui/core/Typography';
import {makeStyles} from '@material-ui/core/styles';
import Container from '@material-ui/core/Container';
import axios from 'axios';
import {useRouter} from 'next/router';
import isMail from 'isemail';

import Link from "../src/components/MaterialNextLink";

const useStyles = makeStyles((theme) => ({
    paper: {
        marginTop: theme.spacing(8),
        display: 'flex',
        flexDirection: 'column',
        alignItems: 'center',
    },
    avatar: {
        margin: theme.spacing(1),
        backgroundColor: theme.palette.secondary.main,
    },
    form: {
        width: '100%', // Fix IE 11 issue.
        marginTop: theme.spacing(1),
    },
    submit: {
        margin: theme.spacing(3, 0, 2),
    },
    switchTypeButton: {
        margin: theme.spacing(0, 0 , 2)
    }
}));

export default function SignIn() {
    const classes = useStyles();
    const router = useRouter();
    const [username, setUsername] = useState("");
    const [password, setPassword] = useState("");
    const isNakUserValid = () => username.length > 0;
    const isMailUserValid = () => isMail.validate(username);
    const isUserValid = () => {
        if (isNakUser) {
            return isNakUserValid();
        } else {
            return isMailUserValid();
        }
    }
    const [isNakUser, setIsNakUser] = useState(true);

    const login = (e: React.FormEvent) => {
        e.preventDefault();
        if (isUserValid()) {
            let req;
            if (isNakUser) {
                req = {type: "ldap", username: username, password: password};
            } else {
                req = {type: "external", email: username, password: password};
            }
            axios.post(`${process.env.NEXT_PUBLIC_BACKEND_URL}/login`, req)
                .then(() => router.push("/"))
                .catch(error => {
                    console.log(error);
                });
        } else {
            console.log("Eingaben waren nicht valide");
        }
    }

    return (
        <Container component="main" maxWidth="xs">
            <CssBaseline/>
            <div className={classes.paper}>
                <Avatar className={classes.avatar}>
                    <LockOutlinedIcon/>
                </Avatar>
                <Typography component="h1" variant="h5">
                    Anmeldung {isNakUser ? '(NAK)' : '(extern)'}
                </Typography>
                <form className={classes.form} onSubmit={login}>
                    <TextField
                        variant="outlined"
                        margin="normal"
                        required
                        fullWidth
                        id="username"
                        label={isNakUser ? 'CIS-Benutzername' : 'E-Mail-Adresse'}
                        name="username"
                        autoComplete="email"
                        autoFocus
                        value={username}
                        onChange={(e) => setUsername(e.target.value)}
                        error={!isUserValid()}
                    />
                    <TextField
                        variant="outlined"
                        margin="normal"
                        required
                        fullWidth
                        name="password"
                        label="Passwort"
                        type="password"
                        id="password"
                        autoComplete="current-password"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                    />
                    <Button
                        type="submit"
                        fullWidth
                        variant="contained"
                        color="primary"
                        className={classes.submit}
                    >
                        Anmelden
                    </Button>
                    <Button
                        type="button"
                        fullWidth
                        variant="contained"
                        color="default"
                        className={classes.switchTypeButton}
                        onClick={() => setIsNakUser(currentValue => !currentValue)}
                    >
                        {isNakUser ? 'Login mit E-Mail' : 'Zum CIS-Login'}
                    </Button>
                    <Grid container>
                        <Grid item xs>
                            <Link href="/registration" variant="body2">Password vergessen?</Link>
                        </Grid>
                        <Grid item>
                            <Link href="/registration" variant="body2">Registrieren</Link>
                        </Grid>
                    </Grid>
                </form>
            </div>
        </Container>
    );
}