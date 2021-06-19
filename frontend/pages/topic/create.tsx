import { Button, Container, TextField, Typography } from '@material-ui/core';
import { makeStyles } from '@material-ui/core/styles';
import { Autocomplete } from '@material-ui/lab';
import { KeyboardDatePicker } from '@material-ui/pickers';
import { useFormik } from 'formik';
import * as yup from 'yup';
import React from 'react';
import Head from 'next/head';
import Link from 'next/link';
import axiosClient from '../../src/api';
import { GetServerSidePropsContext, GetServerSidePropsResult } from 'next';
import { fetchUser } from '../../src/server/fetchUser';
import { serverSideTranslations } from 'next-i18next/serverSideTranslations';
import { useTranslation } from 'next-i18next';

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

export async function getServerSideProps(context: GetServerSidePropsContext): Promise<GetServerSidePropsResult<any>> {
  const user = await fetchUser(context.req.cookies);

  if (user === null) {
    return { redirect: { destination: '/login', permanent: false } };
  }

  return {
    props: { ...(await serverSideTranslations('de', ['common', 'topic-creation'])), user },
  };
}

export default function CreateTopic(): JSX.Element {
  const { t: tCommon } = useTranslation('common');
  const { t: tCreateTopic } = useTranslation('topic-creation');

  const validationSchema = yup.object({
    title: yup.string().required(tCreateTopic('messageTitleRequired')),
    description: yup.string().required(tCreateTopic('messageDescriptionRequired')),
    requirements: yup.string().required(tCreateTopic('messageRequirementsRequired')),
    scope: yup.string().required(tCreateTopic('messageScopeRequired')),
    start: yup.date().nullable(),
    deadline: yup.date().nullable(),
    tags: yup.array().ensure(),
    pages: yup.number().nullable(),
    website: yup.string().nullable().url(tCreateTopic('messageUrlInvalid')),
  });

  const submitForm = async (values: IFormValues): Promise<void> => {
    await axiosClient.post(`/topic`, values);
    // TODO: success message/back navigation? error handling?
  };

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
      console.log('submitting topic!');
      submitForm(values);
    },
  });

  const classes = useStyles();

  return (
    <>
      <Head>
        <title>
          {tCreateTopic('title')} - {tCommon('appName')}
        </title>
      </Head>
      <>
        <Typography gutterBottom variant="h4" component="h2">
          {tCreateTopic('headline')}
        </Typography>
        <form
          noValidate
          onSubmit={(e) => {
            e.preventDefault();
            formik.submitForm();
          }}
        >
          <TextField
            label={tCreateTopic('labelTitel')}
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
            label={tCreateTopic('labelScope')}
            name="scope"
            fullWidth
            className={classes.formField}
            value={formik.values.scope}
            onChange={formik.handleChange}
            helperText={formik.touched.scope && formik.errors.scope}
            error={formik.touched.scope && Boolean(formik.errors.scope)}
            onBlur={formik.handleBlur}
            multiline
            required
          />
          <TextField
            label={tCreateTopic('labelDescription')}
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
            label={tCreateTopic('labelRequirements')}
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
            label={tCreateTopic('labelPages')}
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
            label={tCreateTopic('labelWebsite')}
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
            label={tCreateTopic('labelStartdate')}
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
            label={tCreateTopic('labelEnddate')}
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
                label={tCreateTopic('labelTags')}
                helperText={formik.touched.tags && formik.errors.tags}
                error={formik.touched.tags && Boolean(formik.errors.tags)}
              />
            )}
          />
          <Button variant="contained" color="primary" type="submit">
            {tCreateTopic('buttonSubmit')}
          </Button>
          <Link href="/">
            <Button className={classes.cancelButton} variant="contained" color="default" type="button">
              {tCreateTopic('buttonCancel')}
            </Button>
          </Link>
        </form>
      </>
    </>
  );
}

const tagOptions: string[] = []; // TODO: hard coded defaults or values loaded from the backend?

CreateTopic.layout = 'main';
