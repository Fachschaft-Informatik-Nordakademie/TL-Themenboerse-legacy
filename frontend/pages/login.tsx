import React, { useState } from 'react';
import Avatar from '@material-ui/core/Avatar';
import Button from '@material-ui/core/Button';
import CssBaseline from '@material-ui/core/CssBaseline';
import TextField from '@material-ui/core/TextField';
import Link from '@material-ui/core/Link';
import Grid from '@material-ui/core/Grid';
import LockOutlinedIcon from '@material-ui/icons/LockOutlined';
import Typography from '@material-ui/core/Typography';
import { makeStyles } from '@material-ui/core/styles';
import Container from '@material-ui/core/Container';
import axios from 'axios';
import { useRouter } from 'next/router';
import isMail from 'isemail';
import Switch from '@material-ui/core/Switch';
import FormControlLabel from '@material-ui/core/FormControlLabel';

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
}));

export default function SignIn() {
  const classes = useStyles();
  const router = useRouter();
  const [username, setUsername] = useState("");
  const [password, setPassword] = useState("");
  const [open, setOpen] = useState(false);
  const isNakUserValid = () => username.match("^(?=(\\D*\\d){5}\\D*$)(?=[^ ]* ?[^ ]*$)[\\d ]*$");
  const isMailUserValid = () => isMail.validate(username);
  const isUserValid = () => {
    if(isNakUser){
      return isNakUserValid();
    }
    else{
      return isMailUserValid();
    }
  }
  const [isNakUser,setIsNakUser] = useState(true);
  const [loginVariant,setLoginVariant] = useState("NAK-Login");
  const [usernameLabel,setUsernameValue] = useState("NAK-Nutzername");


  const [state, setState] = React.useState({checked:true});
  const handleChange = (e) => {
    setState({ ...state, [e.target.name]: e.target.checked });
    if(e.target.checked){
      setLoginVariant("NAK-Login");
      setUsernameValue("NAK-Nutzername");
      setIsNakUser(true);
    }
    else{
      setLoginVariant("Email-Login")
      setUsernameValue("E-Mail");
      setIsNakUser(false);
    }
  };

  const login = (e: React.FormEvent) => {
    e.preventDefault();
    if(isUserValid()){
      var req;
      if (isNakUser) {
        req = { type: "ldap", username: username, password: password }; 
      }
      else {
        req = { type: "external", email: username, password: password };
      }
      axios.post(`${process.env.NEXT_PUBLIC_BACKEND_URL}/login`, req)
        .then(response => router.push("/"))
        .catch(error => {
          console.log(error);
        });
    }
    else{
      console.log("Eingaben waren nicht valide");
    }
  }

  return (
    <Container component="main" maxWidth="xs">
      <CssBaseline />
      <div className={classes.paper}>
        <Avatar className={classes.avatar}>
          <LockOutlinedIcon />
        </Avatar>
        <Typography component="h1" variant="h5">
          Anmeldung
        </Typography>
        <FormControlLabel
          control={
            <Switch
             checked={state.checked}
              onChange={handleChange}
              name="checked"
            />
        }
        label={loginVariant}
        />
        <form className={classes.form} onSubmit={login}>
          <TextField
            variant="outlined"
            margin="normal"
            required
            fullWidth
            id="username"
            label={usernameLabel}
            name="username"
            autoComplete="email"
            autoFocus
            value={username}
            onChange={(e) => setUsername(e.target.value)}
            error={isUserValid() ? false : true}
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
          <Grid container>
            <Grid item xs>
              <Link href="#" variant="body2">
                Passwort vergessen?
              </Link>
            </Grid>
            <Grid item>
              <Link href="#" variant="body2">
                Registrieren
              </Link>
            </Grid>
          </Grid>
        </form>
      </div>
    </Container>
  );
}