import { Button, Container, TextField } from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import { Autocomplete } from '@material-ui/lab';
import { KeyboardDatePicker } from '@material-ui/pickers';
import { useFormik } from 'formik';
import * as yup from 'yup';
import React from 'react';
import Head from 'next/head';
import axiosClient from '../../src/api';

interface IFormValues {
  readonly title: string;
  readonly description: string;
  readonly requirements: string;
  readonly pages: number | null;
  readonly start: Date | null;
  readonly deadline: Date | null;
  readonly tags: string[];
  readonly website: string;
}

const useStyles = makeStyles((theme) => ({
  formField: {
    marginTop: theme.spacing(2),
    marginBottom: theme.spacing(2),
  },
  deadlineField: {
    marginLeft: theme.spacing(2),
  },
}));

const validationSchema = yup.object({
  title: yup.string().required('Titel ist ein Pflichtfeld'),
  description: yup.string().required('Beschreibung ist ein Pflichtfeld'),
  requirements: yup.string().required('Beschreibung ist ein Pflichtfeld'),
  start: yup.date().nullable(),
  deadline: yup.date().nullable(),
  tags: yup.array().ensure(),
  pages: yup.number().nullable(),
  website: yup.string().nullable().url(),
});

function CreateTopic(): JSX.Element {
  const submitForm = async (values: IFormValues): Promise<void> => {
    await axiosClient.post(`/topic`, values);
    // TODO: success message/back navigation? error handling?
  };

  const formik = useFormik<IFormValues>({
    initialValues: {
      title: '',
      description: '',
      tags: [],
      start: null,
      deadline: null,
      requirements: '',
      pages: null,
      website: '',
    },
    validationSchema: validationSchema,
    onSubmit: (values) => {
      console.log('submitting topic!');
      submitForm(values);
    },
  });

  const classes = useStyles();

  return (
    <>
      <Head>
        <title>Thema erstellen - Themenbörse</title>
      </Head>
      <Container>
        <h1>Neues Thema erstellen</h1>
        <form
          noValidate
          onSubmit={(e) => {
            e.preventDefault();
            formik.submitForm();
          }}
        >
          <TextField
            label="Titel"
            name="title"
            fullWidth
            className={classes.formField}
            value={formik.values.title}
            onChange={formik.handleChange}
            helperText={formik.touched.title && formik.errors.title}
            error={formik.touched.title && Boolean(formik.errors.title)}
            onBlur={formik.handleBlur}
            required
          />
          <TextField
            label="Beschreibung"
            name="description"
            fullWidth
            multiline
            className={classes.formField}
            value={formik.values.description}
            onChange={formik.handleChange}
            helperText={formik.touched.description && formik.errors.description}
            error={formik.touched.description && Boolean(formik.errors.description)}
            onBlur={formik.handleBlur}
            required
          />
          <TextField
            label="Anforderungen"
            name="requirements"
            fullWidth
            multiline
            className={classes.formField}
            value={formik.values.requirements}
            onChange={formik.handleChange}
            helperText={formik.touched.requirements && formik.errors.requirements}
            error={formik.touched.requirements && Boolean(formik.errors.requirements)}
            onBlur={formik.handleBlur}
            required
          />
          <TextField
            label="Geschätzte Seitenzahl"
            name="pages"
            fullWidth
            type="number"
            className={classes.formField}
            value={formik.values.pages}
            onChange={formik.handleChange}
            helperText={formik.touched.pages && formik.errors.pages}
            error={formik.touched.pages && Boolean(formik.errors.pages)}
            onBlur={formik.handleBlur}
          />
          <TextField
            label="Website"
            name="website"
            fullWidth
            className={classes.formField}
            value={formik.values.website}
            onChange={formik.handleChange}
            helperText={formik.touched.website && formik.errors.website}
            error={formik.touched.website && Boolean(formik.errors.website)}
            onBlur={formik.handleBlur}
          />
          <KeyboardDatePicker
            name="start"
            disableToolbar
            variant="inline"
            format="dd.MM.yyyy"
            margin="normal"
            label="Starttermin"
            value={formik.values.start}
            onChange={(value) => formik.setFieldValue('start', value)}
            helperText={formik.touched.start && formik.errors.start}
            error={formik.touched.start && Boolean(formik.errors.start)}
            onBlur={formik.handleBlur}
          />
          <KeyboardDatePicker
            className={classes.deadlineField}
            name="deadline"
            disableToolbar
            variant="inline"
            format="dd.MM.yyyy"
            margin="normal"
            label="Endtermin"
            value={formik.values.deadline}
            onChange={(value) => formik.setFieldValue('deadline', value)}
            helperText={formik.touched.deadline && formik.errors.deadline}
            error={formik.touched.deadline && Boolean(formik.errors.deadline)}
            onBlur={formik.handleBlur}
          />
          <Autocomplete
            id="tags"
            className={classes.formField}
            multiple
            freeSolo
            value={formik.values.tags}
            onChange={(e, values) => formik.setFieldValue('tags', values)}
            options={tagOptions}
            getOptionLabel={(option) => option}
            defaultValue={[]}
            onBlur={formik.handleBlur}
            renderInput={(params) => (
              <TextField
                {...params}
                variant="standard"
                label="Tags"
                helperText={formik.touched.tags && formik.errors.tags}
                error={formik.touched.tags && Boolean(formik.errors.tags)}
              />
            )}
          />
          <Button variant="contained" color="primary" type="submit">
            Erstellen
          </Button>
        </form>
      </Container>
    </>
  );
}

const tagOptions: string[] = []; // TODO: hard coded defaults or values loaded from the backend?

export default CreateTopic;
