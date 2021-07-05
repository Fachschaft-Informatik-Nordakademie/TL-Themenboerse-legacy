import { PageComponent } from '../../src/types/PageComponent';
import Typography from '@material-ui/core/Typography';
import React, { useState } from 'react';
import TextField from '@material-ui/core/TextField';
import { makeStyles } from '@material-ui/core/styles';
import Button from '@material-ui/core/Button';
import Grid from '@material-ui/core/Grid';
import Link from '../../src/components/MaterialNextLink';
import * as yup from 'yup';
import { useFormik } from 'formik';
import axiosClient from '../../src/api';
import createNotification from '../../src/components/createNotification';

const useStyles = makeStyles((theme) => ({
  introText: {
    marginTop: theme.spacing(2),
  },
  form: {
    width: '100%', // Fix IE 11 issue.
    marginTop: theme.spacing(1),
  },
  submit: {
    margin: theme.spacing(3, 0, 2),
  },
}));

type VerifyFormValues = {
  email: string;
};

const VerifyToken: PageComponent<unknown> = (): JSX.Element => {
  const classes = useStyles();
  const [success, setSuccess] = useState<boolean>(false);
  const [error, setError] = useState<string>(undefined);

  const validationSchema = yup.object().shape({
    email: yup.string().required('Bitte gib deine E-Mail-Adresse ein').email('E-Mail-Adresse ist ungültig'),
  });

  const onSubmit = async (values: VerifyFormValues) => {
    setSuccess(false);
    setError(undefined);

    axiosClient
      .post('/verify-email/resend', {
        email: values.email,
      })
      .then(() => {
        setSuccess(true);
      })
      .catch(() => {
        setError('Ein Fehler ist aufgetreten');
      });
  };

  const form = useFormik<VerifyFormValues>({
    initialValues: {
      email: '',
    },
    validationSchema,
    onSubmit,
  });

  const getFormFieldError = (field: string): string => {
    return form.touched[field] && form.errors[field];
  };

  const hasFormFieldError = (field: string): boolean => {
    return Boolean(getFormFieldError(field));
  };

  return (
    <>
      <Typography component="h1" variant="h5">
        Registrierung bestätigen
      </Typography>
      <Typography component="p" variant="body1" className={classes.introText}>
        Falls dein Bestätigungslink abgelaufen ist oder du keinen erhalten hast, kannst du hier einen neuen anfordern.
      </Typography>
      <form className={classes.form} onSubmit={form.handleSubmit} noValidate>
        {error && createNotification(error, 'error')}
        {success && createNotification('E-Mail wurde gesendet', 'success')}
        <TextField
          variant="outlined"
          margin="normal"
          required
          fullWidth
          id="email"
          label="E-Mail-Adresse"
          name="email"
          autoComplete="email"
          value={form.values.email}
          onChange={form.handleChange}
          onBlur={form.handleBlur}
          error={hasFormFieldError('email')}
          helperText={getFormFieldError('email')}
        />
        <Button type="submit" fullWidth variant="contained" color="primary" className={classes.submit}>
          Senden
        </Button>
      </form>
      <Grid container>
        <Grid item xs>
          <Link href="/login" variant="body2">
            Zurück zum Login
          </Link>
        </Grid>
      </Grid>
    </>
  );
};
VerifyToken.layout = 'auth';

export default VerifyToken;
