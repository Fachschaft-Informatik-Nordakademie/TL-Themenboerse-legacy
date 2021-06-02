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

export default function Registration () {
    const classes = useStyles();

    return <Container maxWidth="sm">
        <Card variant="outlined">
            <CardContent className={classes.content}>
                <Typography variant="h4">Registierung</Typography>
                <form className={classes.form} noValidate>
                    <TextField required id="email" type="email" label="E-Mail" autoComplete="email" spellCheck="false" />
                    <TextField required id="password" type="password" label="Passwort" autoComplete="new-password" spellCheck="false" />
                    <TextField required id="password-confirm" type="password" label="Passwort bestätigen" autoComplete="new-password" spellCheck="false" />
                </form>
            </CardContent>
            <CardActions className={classes.actions}>
            <Button className={classes.submit} variant="contained" color="primary">Registrieren</Button>
            </CardActions>
        </Card>
      </Container>
}