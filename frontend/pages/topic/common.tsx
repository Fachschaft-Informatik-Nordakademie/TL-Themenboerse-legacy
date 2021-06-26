import { Button, TextField, Typography } from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import { Autocomplete } from '@material-ui/lab';
import { KeyboardDatePicker } from '@material-ui/pickers';
import { useFormik } from 'formik';
import * as yup from 'yup';
import React from 'react';
import Head from 'next/head';
import Link from 'next/link';
import { useTranslation } from 'next-i18next';
import { useEffect } from 'react';
import { NextRouter, useRouter } from 'next/router';

interface IFormValues {
  readonly title: string;
  readonly description: string;
  readonly requirements: string;
  readonly scope: string;
  readonly pages: number;
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
  cancelButton: {
    marginLeft: theme.spacing(2),
  },
}));

export function topicForm(
  effect: (router: NextRouter, formik: { setFieldValue: (field: string, value: unknown) => void }) => void,
  submit: (router: NextRouter, values: IFormValues) => void,
): () => JSX.Element {
  return () => {
    const { t: tCommon } = useTranslation('common');
    const { t: tEditTopic } = useTranslation('topic-edit');

    const router = useRouter();

    const validationSchema = yup.object({
      title: yup.string().required(tEditTopic('messageTitleRequired')),
      description: yup.string().required(tEditTopic('messageDescriptionRequired')),
      requirements: yup.string().required(tEditTopic('messageRequirementsRequired')),
      start: yup.date().nullable(),
      deadline: yup.date().nullable(),
      tags: yup.array().ensure(),
      pages: yup.number().nullable(),
      website: yup.string().nullable().url(tEditTopic('messageUrlInvalid')),
    });

    const formik = useFormik<IFormValues>({
      initialValues: {
        title: '',
        description: '',
        scope: '',
        tags: [],
        start: null,
        deadline: null,
        requirements: '',
        pages: 0,
        website: '',
      },

      validationSchema: validationSchema,
      onSubmit: (values) => {
        submit(router, values);
      },
    });

    useEffect(() => {
      effect(router, formik);
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    const classes = useStyles();

    return (
      <>
        <Head>
          <title>
            {tEditTopic('title')} - {tCommon('appName')}
          </title>
        </Head>
        <>
          <Typography gutterBottom variant="h4" component="h2">
            {tEditTopic('headline')}
          </Typography>
          <form
            noValidate
            onSubmit={(e) => {
              e.preventDefault();
              formik.submitForm();
            }}
          >
            <TextField
              label={tEditTopic('labelTitel')}
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
              label={tEditTopic('labelDescription')}
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
              label={tEditTopic('labelRequirements')}
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
              label={tEditTopic('labelPages')}
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
              label={tEditTopic('labelWebsite')}
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
              label={tEditTopic('labelStartdate')}
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
              label={tEditTopic('labelEnddate')}
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
                  label={tEditTopic('labelTags')}
                  helperText={formik.touched.tags && formik.errors.tags}
                  error={formik.touched.tags && Boolean(formik.errors.tags)}
                />
              )}
            />
            <Button variant="contained" color="primary" type="submit">
              {tEditTopic('buttonSubmit')}
            </Button>
            <Link href="/">
              <Button className={classes.cancelButton} variant="contained" color="default" type="button">
                {tEditTopic('buttonCancel')}
              </Button>
            </Link>
          </form>
        </>
      </>
    );
  };
}

const tagOptions: string[] = []; // TODO: hard coded defaults or values loaded from the backend?
